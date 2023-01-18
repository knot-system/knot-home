<?php

if( ! $sekretaer ) exit;

snippet( 'header' );

?>

<h2>Dashboard</h2>

<pre>
<?php var_dump($_SESSION); ?>
</pre>


<?php

snippet( 'footer' );
