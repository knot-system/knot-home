<?php

// update: 2023-05-16


class Image {

	private $type;
	private $local_file_path;
	private $image_url;
	private $orientation = 9; // NOTE: EXIF orientation, see https://exiftool.org/TagNames/EXIF.html -- 1 = 0°, 8 = 90°, 3 = 180°, 6 = 270°/-90°; 2, 7, 4, 5 is the same, but mirrored horizontally before rotating; 9 means undefined
	private $rotated = false;
	private $image_type;
	private $src_width;
	private $src_height;
	private $format;
	private $mime_type;

	function __construct( $image_path, $type = false ) {

		global $core;

		$this->type = $type;

		if( $this->type == 'remote' ) {
			$this->local_file_path = $core->abspath.'cache/remote-image/'.$image_path;
			$this->image_url = $core->baseurl.'remote-image/'.$image_path;
		} else {
			$this->local_file_path = $core->abspath.$image_path;
			$this->image_url = $core->baseurl.$image_path;
		}

		if( ! file_exists( $this->local_file_path) ) {
			var_dump('not exists');
			$core->debug("local image file does not exist", $this->local_file_path);
			return false;
		}

		$exif = @exif_read_data( $this->local_file_path );
		if( $exif && ! empty($exif['Orientation']) ) {
			$this->orientation = $exif['Orientation'];
		}

		if( $this->orientation == 6 || $this->orientation == 8 || $this->orientation == 5 || $this->orientation == 7 ) {
			$this->rotated = true;
		}

		$image_meta = getimagesize( $this->local_file_path );
		if( ! $image_meta ) {
			$core->debug("no image meta", $this->local_file_path);
			return false;
		}

		$this->src_width = $image_meta[0];
		$this->src_height = $image_meta[1];
		$this->image_type = $image_meta[2];

		if( $this->src_width > $this->src_height ) {
			$this->format = 'landscape';
		} elseif( $this->src_width < $this->src_height ) {
			$this->format = 'portrait';
		} else {
			$this->format = 'square';
		}


		if( $this->image_type == IMAGETYPE_JPEG
		 || ($core->config->get('image_png_to_jpg') && $this->image_type == IMAGETYPE_PNG) ) {

			$this->mime_type = 'image/jpeg';

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			$this->mime_type = 'image/png';

		} else {

			$core->debug( 'unknown image type '.$this->image_type);
			exit;

		}

	}


	function get_html_embed( $target_width = false ) {

		global $core;

		if( ! $target_width ) {
			$target_width = $core->config->get( 'image_target_width' );
		}

		$image_url = $this->image_url;
		$image_url .= '?width='.$target_width;

		$src_width = $this->src_width;
		$src_height = $this->src_height;

		if( $this->rotated ) {
			$tmp_width = $src_width;
			$src_width = $src_height;
			$src_height = $tmp_width;
		}

		list( $width, $height ) = $this->get_image_dimensions( $target_width, $src_width, $src_height );

		$classes = array( 'content-image', 'content-image-format-'.$this->format );

		$preview_base64 = $this->get_image_preview_base64();

		$image_embed_html = '<figure class="'.implode(' ', $classes).'" style="aspect-ratio: '.$width/$height.'">
				<span class="content-image-inner"';
				if( $preview_base64 ) $image_embed_html .= ' style="background-image: url('.$preview_base64.');"';
				$image_embed_html .= '>
					<img src="'.$image_url.'" width="'.$width.'" height="'.$height.'" loading="lazy" style="background: transparent; display: block;">
				</span>
			</figure>';

		return $image_embed_html;
	}


	function display() {

		global $core;

		$target_width = $core->config->get( 'image_target_width' );

		if( isset($_GET['width']) ) $target_width = (int) $_GET['width'];
		if( $target_width < 1 ) $target_width = 10;

		$jpg_quality = $core->config->get( 'image_jpg_quality' );
		$filesize = filesize( $this->local_file_path );

		$cache_string = $this->local_file_path.$filesize.$target_width.$jpg_quality;
		$cache = new Cache( 'image', $cache_string );
		$cache_content = $cache->get_data();
		if( $cache_content ) {
			// return cached file, then end
			header("Content-Type: ".$this->mime_type);
			header("Content-Length: ".$cache->filesize);
			echo $cache_content;
			exit;
		}

		$src_image = $this->load_image_data();


		$src_width = $this->src_width;
		$src_height = $this->src_height;

		list( $src_image, $src_width, $src_height ) = $this->image_rotate( $src_image, $src_width, $src_height );

		$png_to_jpg = $core->config->get( 'image_png_to_jpg' );
		
		if( $src_width > $target_width ) {
			
			list( $width, $height ) = $this->get_image_dimensions( $target_width );

			if( $this->rotated ) {
				$tmp_width = $width;
				$width = $height;
				$height = $tmp_width;
			}

			$target_image = imagecreatetruecolor( $width, $height );

			if( ! $png_to_jpg && $this->image_type == IMAGETYPE_PNG ) {
				// handle alpha channel
				imageAlphaBlending( $target_image, false );
				imageSaveAlpha( $target_image, true );
			}

			imagecopyresized( $target_image, $src_image, 0, 0, 0, 0, $width, $height, $src_width, $src_height );

		} else {

			$width = $src_width;
			$height = $src_height;

			$target_image = $src_image;
		}

		imagedestroy($src_image);


		if( $this->image_type == IMAGETYPE_JPEG
		 || ($png_to_jpg && $this->image_type == IMAGETYPE_PNG) ) {

			ob_start();
			imagejpeg( $target_image, NULL, $jpg_quality );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: '.$this->mime_type );
			echo $data;

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			ob_start();
			imagepng( $target_image );
			$data = ob_get_contents();
			ob_end_clean();
			$cache->add_data( $data );

			header( 'Content-Type: '.$this->mime_type );
			echo $data;

		}

		imagedestroy( $target_image );
		exit;

	}


	function get_image_preview_base64() {

		global $core;

		if( ! $core->config->get( 'image_png_to_jpg' ) ) {
			// NOTE: when we use png files directly (and don't convert them to jpg), they could contain transparency. if they do, we cannot add a blurry preview base64 encoded image beneath it, because it would still be visible when the actual image (with transparency) is loaded.
			return false;
		}

		$filesize = filesize( $this->local_file_path );
		if( ! $filesize ) {
			$core->debug("no image filesize", $this->local_file_path);
			return false;
		}

		$cache_string = $this->local_file_path.$filesize;

		$cache = new Cache( 'image-preview', $cache_string );

		$cache_content = $cache->get_data();
		if( $cache_content ) {
			// return cached file, then end
			return $cache_content;
		}


		$target_width = 50;
		$jpg_quality = 40;

		$src_image = $this->load_image_data();

		$src_width = $this->src_width;
		$src_height = $this->src_height;

		list( $src_image, $src_width, $src_height ) = $this->image_rotate( $src_image, $this->src_width, $this->src_height );

		list( $width, $height ) = $this->get_image_dimensions( $target_width );

		if( $this->rotated ) {
			$tmp_width = $width;
			$width = $height;
			$height = $tmp_width;
		}

		$target_image = imagecreatetruecolor($width, $height);
		imagecopyresized($target_image, $src_image, 0, 0, 0, 0, $width, $height, $this->src_width, $this->src_height);

		for( $i = 0; $i < 5; $i++ ) {
			imagefilter( $target_image, IMG_FILTER_GAUSSIAN_BLUR );
		}

		imagedestroy($src_image);

		ob_start();
		imagejpeg( $target_image, NULL, $jpg_quality );
		$image_data = ob_get_contents();
		ob_end_clean();

		$base64_data = 'data:image/jpeg;base64,'.base64_encode($image_data);

		$cache->add_data( $base64_data );

		imagedestroy( $target_image );

		return $base64_data;
	}


	function load_image_data() {

		global $core;

		$image = false;

		if( $this->image_type == IMAGETYPE_JPEG ) {

			$image = imagecreatefromjpeg( $this->local_file_path );

		} elseif( $this->image_type == IMAGETYPE_PNG ) {

			$image = imagecreatefrompng( $this->local_file_path );

			if( ! $image ) {
				$core->debug( 'could not load png image' );
				exit;
			}

			// handle transparency loading:
			imageAlphaBlending( $image, false );
			imageSaveAlpha( $image, true );
		
			if( $core->config->get( 'image_png_to_jpg' ) ) {
				// set transparent background to specific color, when converting to jpg:
				$transparent_color = $core->config->get( 'image_background_color' );
				$background_image = imagecreatetruecolor( $this->src_width, $this->src_height );
				$background_color = imagecolorallocate( $background_image, $transparent_color[0], $transparent_color[1], $transparent_color[2] );
				imagefill( $background_image, 0, 0, $background_color );
				imagecopy( $background_image, $image, 0, 0, 0, 0, $this->src_width, $this->src_height );
				$image = $background_image;
				imagedestroy( $background_image );
			}

		}


		if( ! $image ) {
			$core->debug( 'could not load image with mime-type '.$this->image_type );
			exit;
		}

		return $image;
	}


	function get_image_dimensions( $target_width, $src_width = false, $src_height = false ) {

		if( ! $src_width ) $src_width = $this->src_width;
		if( ! $src_height ) $src_height = $this->src_height;

		if( $src_width <= $target_width ) {
			return array( $src_width, $src_height );
		}
		
		$width = $target_width;
		$height = (int) round($src_height/$src_width*$width);

		if( $width <= 0 || $height <= 0 ) {
			global $core;
			$core->debug( 'width or height <= 0', $width, $height );
			exit;
		}

		return array( $width, $height );
	}

	
	function image_rotate( $image, $src_width, $src_height ) {

		$width = $src_width;
		$height = $src_height;

		$degrees = false;
		// NOTE: we ignore mirrored images (4, 5, 7) for now, and just rotate them like they would be non-mirrored (3, 6, 8)
		if( $this->orientation == 3 || $this->orientation == 4 ) {
			$degrees = 180;
		} elseif( $this->orientation == 6 || $this->orientation == 5 ) {
			$degrees = 270;
			$width = $src_height;
			$height = $src_width;
		} elseif( $this->orientation == 8 || $this->orientation == 7 ) {
			$degrees = 90;
			$width = $src_height;
			$height = $src_width;
		}

		if( $degrees ) $image = imagerotate( $image, $degrees, 0 );

		return [ $image, $width, $height ];
	}


}
