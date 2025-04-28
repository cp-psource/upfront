<?php
/**
* Überschreibung des Standardsuchformulars.
  * Implementiert das Wrapping der Submit-Schaltfläche.
  * Kann weiter überschrieben werden, indem diese Vorlage in untergeordnete Themen implementiert wird.
 */
?>
<form role="search" method="get" class="search-form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php echo _x( 'Suchen nach:', 'label' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Suche &hellip;', 'placeholder' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Suchen nach:', 'label' ); ?>" />
	</label>
	<div class="upfront-search-submit_group">
		<input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Suche', 'submit button' ); ?>" />
	</div>
</form>