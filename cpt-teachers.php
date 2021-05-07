<?php
/*
 * Plugin Name: Vamos Plugin for Teachers Custom Post Type and Teachers Widget
 * Version: 2.1
 * Plugin URI: http://amadidesign.co.uk
 * Description:  As of 2013-03-07. As widget to display the custom post type Teachers on widget areas. As plugin thru shortcode on content areas. Note: Needs Custom Post Type UI Plugin installed.
 * Author: Amadidigital
 * Author URI: http://amadidesign.co.uk/
 */

DEFINE( 'CPT_TEACHER_ROOT', dirname( __FILE__ ) . '/' );

class CPTTeacherWidget extends WP_Widget
{
 /**
  * Declares the widget class.
  *
  */
    public function __construct() {
      $widget_ops = array('classname' => 'widget_teacher', 'description' => __( "Display Teachers Post") );
      $control_ops = array('width' => 300, 'height' => 300);
      parent::__construct('cptteacherwidget', __('Display Teachers Post'), $widget_ops, $control_ops);
    }

  /**
    * Displays the Widget
    *
    */
    function widget($args, $instance){
      extract($args);
      $title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
      $showType = empty($instance['showType']) ? 'all' : $instance['showType'];
      $showText = empty($instance['showText']) ? 'excerpt' : $instance['showText'];
      $thePlugin = new CPTTeacherPlugin();
      $content = $thePlugin->prepareContent($showType,$showText);

      # Before the widget
      echo $before_widget;
      # The title
      if ( $title ){
        echo $before_title . $title . $after_title;
      }
      echo $content;

      # After the widget
      echo $after_widget;
  }

  /**
    * Saves the widgets settings.
    *
    */
    function update($new_instance, $old_instance){
      $instance = $old_instance;
      $instance['title'] = strip_tags(stripslashes($new_instance['title']));
      $instance['showType'] = strip_tags(stripslashes($new_instance['showType']));
      $instance['showText'] = strip_tags(stripslashes($new_instance['showText']));

    return $instance;
  }

  /**
    * Creates the edit form for the widget.
    *
    */
    function form($instance){
      //Defaults
      $instance = wp_parse_args( (array) $instance,
        array(
          'title'=>'',
          'showType'=>'all',
          'showText'=>'excerpt'
        )
      );

      $title = htmlspecialchars($instance['title']);
      $showType = htmlspecialchars($instance['showType']);
      $showText = htmlspecialchars($instance['showText']);

      $showTypes=array('','');
      $show=0;
      if ($showType=='all') {
        $show=0;
      } else {
        $show=1;
      }
      $showTypes[$show]=' selected="selected" ';

      $showTexts=array('','');
      $show=0;
      if ($showText=='excerpt') {
        $show=0;
      } else {
        $show=1;
      }
      $showTexts[$show]=' selected="selected" ';

      # Output the options
      echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . '</label><input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';
      echo '<p style="text-align:right;"><label for="' . $this->get_field_name('showType') . '">' . __('Show Type:') . '</label>
        <select name="' . $this->get_field_name('showType').'" id="'.$this->get_field_id('showType').'">
          <option value="all" '.$showTypes[0].'>All</option>
          <option value="random" '.$showTypes[1].'>Random (single)</option>
        </select>
      </p>';
      echo '<p style="text-align:right;"><label for="' . $this->get_field_name('showText') . '">' . __('Content Type:') . '</label>
        <select name="'.$this->get_field_name('showText').'" id="'.$this->get_field_id('showText').'">
          <option value="excerpt" '.$showTexts[0].'>Excerpt</option>
          <option value="content" '.$showTexts[1].'>Content</option>
        </select>
      </p>';
  }

  /**
    * Miscelaneous internal functions
    *
    */
}// END class

/**
  * Register the widget.
  *
  * Calls 'widgets_init' action after the widget has been registered.
  */
  function CPTTeacherWidgetInit() {
    register_widget('CPTTeacherWidget');
  }
  add_action('widgets_init', 'CPTTeacherWidgetInit');


class CPTTeacherPlugin
{

  function getHTML($aTeacher, $showText){
    ob_start();
    include( CPT_TEACHER_ROOT . '/view.html.php' );
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  } // function


  function getTeachers($limit=10){
    global $post;
    if ($limit>0) {
      $args = array( 'post_type' => 'teacher', 'posts_per_page' => $limit );
    } else {
      $args = array( 'post_type' => 'teacher','posts_per_page' => -1 );
    }
    $teachers=array();
    $origpost=$post;
    $cptloop = new WP_Query( $args );
    while ( $cptloop->have_posts() ) : $cptloop->the_post();
      $theID = get_the_ID();
      $teacher = get_post($theID);
      if (has_post_thumbnail( $theID ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $theID ), 'full' );
        $imageURL = $img[0];
        $width = $img[1];
        $height = $img[2];
      } else {
        $imageURL='';
      }
      $teacher->imageURL=$imageURL;
      $teachers[]=$teacher;
    endwhile;
    $post=$origpost;

    return($teachers);
  }

  public function prepareContent($showType,$showText) {
	$SESH = array();
    $aTeachers = $this->getTeachers(0);
    $aCount=count($aTeachers);
	
    $theContent='';
    if ($aTeachers){
      $tID=0;
      $teachers=array();
//      session_start();
      if ($showType=='random'){
        $SESH['CPTTeachers']['show']='random';
        $tID=$SESH['CPTTeachers']['tID'];
        do {
          srand();
          $id=rand(1,$aCount);
        } while ($id==$tID);
        $tID=$id;
        $SESH['CPTTeachers']['tID']=$tID;
        $aTeacher = $aTeachers[$tID-1];
        $teacher=$this->getHTML( $aTeacher, $showText );
        $teachers[]=$teacher;
      } elseif($showType=='all') {
        $tID=-1;
        $SESH['CPTTeachers']['show']='all';
        foreach( $aTeachers as $aTeacher ){
          $teacher=$this->getHTML( $aTeacher, $showText );
          if ($teacher){
            $teachers[]=$teacher;
          } //teacher
        } //foreach
      } else {
        $sTeacher=$showType;
        $showType='id';
        $i=0;
        foreach( $aTeachers as $aTeacher ){
          $i++;
          if ($aTeacher->post_name==$sTeacher){
            $teacher=$this->getHTML( $aTeacher, $showText );
            if ($teacher){
              $teachers[]=$teacher;
            } //teacher
            break;
          }
        } //foreach
      } //showType
      $theContent=implode('',$teachers);
    } //aTeachers
    return $theContent;
  } //function

  /**
    * Called from showContent to replace special text: [Teachers *] with Teachers.
    * Recognizes this marks (instead of *):
    * - all:    All teachers (default)
    * - random: Single random Teacher
    * - options:
    *    -- excerpt - show excerpt as text (default)
    *    -- content - show content as text
    */
  public function checkShortcodes( $aMatches ){
	//echo 'checkShortCodes=>';print_r($aMatches); 
  
    if( !isset( $aMatches[0] ) ) return '';
	
    if( $aMatches[0]=='#_ATT{Teacher}' ) {
		return '[Teacher "'.$aMatches[0].'" '.$aMatches[1].']';
	}

    $aOptions = array();
    $strReturn = '';
    $aSubMatch = array();

    $showType='?';
    if( preg_match( '/random/', $aMatches[0], $aSubMatch ) ){
      $showType='random';
    }
    if( preg_match( '/all/', $aMatches[0], $aSubMatch ) ){
      $showType='all';
    }
    if ( ($showType!='random') && ($showType!='all')){
      $showType=trim(str_replace('"','',$aMatches[0]));
    }

    $showText='content';
    if( !empty($aMatches[1]) && preg_match( '/excerpt/', $aMatches[1], $aSubMatch ) ){
      $showText='excerpt';
    }

    $content = $this->prepareContent($showType,$showText);
    return $content;
  } //function

  public function getShortcode( $strText ){
    $regex='/\[Teacher (.*?)\]/i';
	preg_match( $regex, $strText, $atts );
    $strText = $this->checkShortcodes($atts);
    return $strText;
  }

   public function insertCSS(){
     $viewcss_url=plugins_url('view.css',__FILE__);
     echo '<link rel="stylesheet" href="'.$viewcss_url.'" type="text/css" media="screen" charset="utf-8" />'."\n";
   }


}// END class
/**
  * Register as plugin.
  *
  */
  $objCPTPlugin = new CPTTeacherPlugin();
//  add_filter( 'the_content', array( &$objCPTPlugin, 'getShortcode' ) );
//  add_filter( 'widget_text', array( &$objCPTPlugin, 'getShortcode' ) );
  add_action( 'wp_head', array( &$objCPTPlugin, 'insertCSS' ) );

  add_shortcode( 'Teacher', 'cpt_teachers_tag_func' );
  function cpt_teachers_tag_func( $atts ) {
    $objCPTPlugin = new CPTTeacherPlugin();
    $html=$objCPTPlugin->checkShortcodes($atts);
    return $html;
  }

?>