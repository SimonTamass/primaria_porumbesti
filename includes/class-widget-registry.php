<?php
namespace PrimariaPorumbesti;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Widget_Registry {
	private const WIDGETS = array(
		'Site_Header'         => 'site-header',
		'Site_Footer'         => 'site-footer',
		'Accessibility_Tools' => 'accessibility-tools',
		'Home_Hero'           => 'home-hero',
		'Page_Hero'           => 'page-hero',
		'Section_Heading'     => 'section-heading',
		'Services_Grid'       => 'services-grid',
		'Content_Media'       => 'content-media',
		'Person_Profile'      => 'person-profile',
		'Schedule_Grid'       => 'schedule-grid',
		'Council_Members'     => 'council-members',
		'Link_List'           => 'link-list',
		'Contact_Details'     => 'contact-details',
		'Contact_Form'        => 'contact-form',
		'Cta_Banner'          => 'cta-banner',
		'Photo_Gallery'       => 'photo-gallery',
		'Data_Table'          => 'data-table',
		'Stats_Bars'          => 'stats-bars',
		'News_Grid'           => 'news-grid',
		'Document_Grid'       => 'document-grid',
		'Document_Library'    => 'document-library',
		'Post_Archive'        => 'post-archive',
		'Single_Post'         => 'single-post',
		'Search_Box'          => 'search-box',
	);

	public static function register( $widgets_manager ): void {
		require_once PORUMBESTI_WIDGETS_PATH . 'includes/widgets/class-base.php';

		foreach ( self::WIDGETS as $class_name => $file_name ) {
			require_once PORUMBESTI_WIDGETS_PATH . 'includes/widgets/class-' . $file_name . '.php';
			$class = __NAMESPACE__ . '\\Widgets\\' . $class_name;
			$widgets_manager->register( new $class() );
		}
	}
}
