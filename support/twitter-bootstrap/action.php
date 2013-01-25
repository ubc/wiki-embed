<?php
/**
 *
 * Files are added whem the the theme has 
 * add_theme_support( 'tabs' , array('twitter-bootstap') );
 * add_theme_support( 'accordion' , array('twitter-bootstap') );
 */

add_filter( 'wiki-embed-tab_list', array( 'Wiki_Embed_Twitter_Bootstrap_Support', 'tab_list' ));
add_filter( 'wiki-embed-articles', array( 'Wiki_Embed_Twitter_Bootstrap_Support', 'articles') , 10, 2 );
add_filter( 'wiki-embed-article-content', array( 'Wiki_Embed_Twitter_Bootstrap_Support', 'article_content' ), 10, 4  );
add_filter( 'wiki-embed-article-content-class', array( 'Wiki_Embed_Twitter_Bootstrap_Support', 'article_content_class' ), 10, 3  );
add_filter( 'wiki-embed-tabs-shell-class', array( 'Wiki_Embed_Twitter_Bootstrap_Support', 'tab_list_class' ));


class Wiki_Embed_Twitter_Bootstrap_Support{
	
	/**
	 * tab_list function.
	 * 
	 * @access public
	 * @param mixed $tab_list
	 * @return void
	 */
	function tab_list( $tab_list ) {
		
		$tab_list = str_replace('<a href="#fragment', '<a data-toggle="tab" href="#fragment', $tab_list );
		$tab_list = substr( $tab_list, 4 );
		
		return '<li class="active">'.$tab_list;
	}
	
	function tab_list_class( $class ) {
		$class .= ' nav nav-tabs ';
		return $class;
	}
	
	
	
	
	/**
	 * articles function.
	 * 
	 * @access public
	 * @param mixed $content
	 * @param mixed $type
	 * @return void
	 */
	function articles( $content, $type ){
		
		switch( $type ) {
			case 'tabs':
				$content = '<div class="tab-content">'.$content."</div>";
				break;
			
			case 'accordion':
				// $content = '<div class="accordion-group">'.$content."</div>";
				break;
		
		}

		return $content;
	
	}
	
	function article_content( $content, $index, $type, $global_count ){
		
		switch( $type ) {
			case 'tabs':
				
				break;
			
			case 'accordion':
				$content = str_replace('<h2><!-- start of headline wiki-embed --><a href="#">', '<div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-wiki-'.$global_count.'" href="#fragment-'.$global_count.'-'.$index.'">', $content );
				$content = str_replace('</a><!--end of headline wiki-embed --></h2>', '</a></div><!--end of headline -->', $content );
				
				$content = str_replace('<!-- start of content headline --><div ','<!-- start of content headline --><div id="fragment-'.$global_count.'-'.$index.'" ', $content );
				
				$content = str_replace('<!-- start of content wiki-embed -->', '<!-- start of content wiki-embed--><div class="accordion-inner" >', $content );
				$content = str_replace('<!-- end of content wiki-embed -->', '</div><!-- end of content wiki-embed -->', $content );
				
				$content = '<div class="accordion-group" >'.$content."</div>";
				break;
		
		}

		return $content;
	
	}
	
	/**
	 * article_content_class function.
	 * 
	 * @access public
	 * @param mixed $class
	 * @param mixed $index
	 * @param mixed $type
	 * @return void
	 */
	function article_content_class( $class, $index, $type ){
		
		switch( $type ) {
			case 'tabs':
				
				if( 0 == (int)$index )
					$class .= " active";
				
				$class .= " tab-pane ";
				
				break;
			
			case 'accordion':
				if( 0 == (int)$index )
					$class .=  ' in ';
				$class .=' accordion-body collapse ';
				
				break;
		
		}

		return $class;
	
	}
	
	
	

}