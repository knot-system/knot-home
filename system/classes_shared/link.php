<?php

// update: 2023-05-19


class Link {

	public $id;
	public $url;
	public $short_url;

	private $cache;

	function __construct( $input, $use_id = false ) {

		if( $use_id ) {

			$id = $input;

			$this->id = $id;
			$this->cache = new Cache( 'link', $id, true );

			$data = $this->cache->get_data();

			if( ! $data ) {
				throw new Exception( 'no data' );
			}

			$data = json_decode($data);

			$this->url = $data->url;

		} else {

			$url = $input;

			$this->url = $url;
			$this->cache = new Cache( 'link', $url );
			$this->id = 'link-'.$this->cache->hash;

			$data = $this->cache->get_data();
			if( ! $data ) {
				// create cache file with basic info
				$data = [
					'id' => $this->id,
					'url' => $url
				];
				$this->update_preview( $data );
			}

		}

		$this->short_url = str_replace(array('https://','http://'), '', $this->url);
		$this->short_url = un_trailing_slash_it($this->short_url);

		$tiny_url = explode("/", $this->short_url);
		$this->tiny_url = un_trailing_slash_it($tiny_url[0]);
		
		return $this;
	}


	function get_preview() {

		$cache_content = $this->cache->get_data();

		if( ! $cache_content ) return false;

		$data = json_decode($cache_content, true);


		$preview_title = '<span class="link-preview-title">'.$this->tiny_url.'</span>';
		$preview_image = '';
		$preview_description = '';
		if( ! empty($data['preview_image']) ) $preview_image = '<span class="link-preview-image">'.$data['preview_image'].'</span>';
		if( ! empty($data['title']) ) $preview_title = '<span class="link-preview-title">'.$data['title'].'</span>';
		if( ! empty($data['description']) ) $preview_description = '<span class="link-preview-description">'.$data['description'].'</span>';
		$preview_html = $preview_image.'<span class="link-preview-text">'.$preview_title.$preview_description;
		$preview_html .= ' <span class="link-preview-url">'.$this->short_url.'</span>';


		$data['preview_html'] = $preview_html;
		$data['preview_html_hash'] = get_hash( $preview_html );

		return $data;
	}


	function update_preview( $data ) {

		$json = json_encode( $data );

		$this->cache->add_data($json);

		return $this;
	}


	function get_info() {

		$url = $this->url;

		$html = request_get_remote( $url );

		// TODO: maybe we want to get information from other meta tags as well. revisit this in the future

		$dom = new Dom( $html );

		$titles = $dom->find_elements('title')->return_elements();

		$title = false;
		if( count($titles) ) $title = $titles[0];
		

		$descriptions = $dom->find_elements('meta')->filter_elements('name', 'description')->return_elements('content');
		$descriptions = array_merge( $descriptions, $dom->find_elements('meta')->filter_elements('property', 'og:description')->return_elements('content') );
		$descriptions = array_merge( $descriptions, $dom->find_elements('meta')->filter_elements('property', 'twitter:description')->return_elements('content') );

		$description = false;
		if( count($descriptions) ) $description = $descriptions[0];


		$preview_image = false;
		$preview_images = $dom->find_elements('meta')->filter_elements('property', 'og:image')->return_elements('content');
		$preview_images = array_merge( $preview_images, $dom->find_elements('meta')->filter_elements('property', 'og:description:url')->return_elements('content') );
		$preview_images = array_merge( $preview_images, $dom->find_elements('meta')->filter_elements('property', 'twitter:image')->return_elements('content') );

		if( count($preview_images) ) {
			$preview_image = $preview_images[0];

			$cache_file_name = $this->get_remote_image( $preview_image );

			global $core;
			$target_width = get_config('preview_target_width' );

			$image = new Image( $cache_file_name, 'remote' );
			$preview_image = $image->get_html_embed( $target_width );

		}

		$data = [
			'id' => $this->id,
			'url' => $url,
			'title' => $title,
			'preview_image' => $preview_image,
			'description' => $description,
			'last_refresh' => time()
		];

		$this->update_preview( $data );

		return $this;
	}

	function get_remote_image( $preview_image ) {

		$preview_image_name = explode('/', $preview_image);
		$preview_image_name = end($preview_image_name);
		$preview_image_name = explode("?", $preview_image_name);
		$preview_image_name = $preview_image_name[0];

		$preview_image_cache = new Cache( 'remote-image', $preview_image_name );
		$preview_image_cache->get_remote_file( $preview_image );

		return $preview_image_cache->cache_file_name;
	}

};
