<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.application.component.helper' );

class  plgSystemPhocaFont extends JPlugin
{

    public $plugin_number  = 0;

   public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

    public function setPluginNumber() {
		$this->plugin_number = (int)$this->plugin_number + 1;
	}

   function onAfterRender() {

		$document   = Factory::getDocument();
		$doctype	= $document->getType();
		$db			= Factory::getDBO();
		$app       	= Factory::getApplication();
		$component	= 'com_phocafont';
		$t		= array();

		if (!ComponentHelper::isEnabled($component)) {
			echo Text::_('Phoca Font Plugin requires Phoca Font Component');
			return true;
		}

		if($app->getName() != 'site') {
			return true;
		}

		if ( $doctype !== 'html' ){
			return true;
		}



		$component			= 'com_phocafont';
		$paramsC			= ComponentHelper::getParams($component) ;

		//$data['params'] 	= $paramsC->toArray();
		//$table 				= JTable::getInstance('extension');
		//$idCom				= $table->find( array('element' => $component ));
		//$table->load($idCom);

		// Default FONT - Parameters Component
		$t['idfont']			= '';
		$t['tagidclass']    	= $paramsC->get('tag_id_class', 'body');
		$t['fontsize']      	= $paramsC->get('font_size', '');
		$t['addcss']      		= $paramsC->get('additional_css', '');
		$t['menuselection']		= $paramsC->get('menu_selection', '');
		$this->onAfterRenderCssRulesWrite($db , $t) ;

		// Second Font
		$t['idfont']    		= $paramsC->get('fonts_02', '');
		$t['tagidclass']    	= $paramsC->get('tag_id_class_02', '');
		$t['fontsize']    		= $paramsC->get('font_size_02', '');
		$t['addcss']      		= $paramsC->get('additional_css_02', '');
		$t['menuselection']  	= $paramsC->get('menu_selection_02', '');
		if ($t['idfont'] != '' && (int)$t['idfont'] != 0 && $t['tagidclass'] != '') {
			$this->onAfterRenderCssRulesWrite($db , $t);
		}
		  // Third Font
		$t['idfont']    		= $paramsC->get('fonts_03', '');
		$t['tagidclass']    	= $paramsC->get('tag_id_class_03', '');
		$t['fontsize']    		= $paramsC->get('font_size_03', '');
		$t['addcss']      		= $paramsC->get('additional_css_03', '');
		$t['menuselection']  	= $paramsC->get('menu_selection_03', '');
		if ($t['idfont'] != '' && (int)$t['idfont'] != 0 && $t['tagidclass'] != '') {
			$this->onAfterRenderCssRulesWrite($db , $t);
		}

		return true ;
   }

   function onAfterRenderCssRulesWrite($db , $t) {

      if (empty($t['tagidclass'])) {
         return true ;
      }

      $app = Factory::getApplication();
      // Itemid check - - - - -
      $ItemId      = $app->input->get('Itemid', 1, 'int');
      if ($t['menuselection'] != '') {
         $t['menuselectionarray'] = explode(',', $t['menuselection']);
      }

      if (!empty($t['menuselectionarray'])) {
         $key = array_search($ItemId, $t['menuselectionarray']);
         if ($key === false) {
            return true;
         }
      }

      $where 	= array();
      $where[] 	= 'a.published = 1';

      if (empty($t['idfont'])) {
         $where[] = 'a.defaultfont = 1';
      } else {
         $where[] = 'a.id = '. $t['idfont'];
      }

      $where       = ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
      $query = 'SELECT a.*'
            .' FROM #__phocafont_font AS a '
            . $where;

      $db->setQuery( $query );
      $fontData = $db->loadObject();

      $css    = '';
      $cssIe   = '';
      if (isset($fontData) && !empty($fontData)) {

		// Alternative
		$alternative = '';
		if (isset($fontData->alternative) && $fontData->alternative !='') {
		   $alternative = ', '.$fontData->alternative;
		}
        // Font Size
		$cssFontSize = '';
		if($t['fontsize'] !='') {
		   $cssFontSize = ' font-size: '.$t['fontsize'].';';
		}

		// Additional
		$cssAdd = '';
		if($t['addcss'] !='') {
		   $cssAdd = ' '.strip_tags($t['addcss']);
		}




         if (isset($fontData->format) && $fontData->format == 'externalfonttype') {

             // EXTERNAL FONT

             if(isset($fontData->title) && $fontData->title !='') {

				$fontData->title = htmlspecialchars($fontData->title);

				$variant = '';
				if(isset($fontData->variant) && $fontData->variant !='') {
					$variant = ':'. str_replace(' ', '', $fontData->variant);
					$variant = htmlspecialchars($variant);
				}

				$subset = '';
				if (isset($fontData->subset) && $fontData->subset != '') {
					$subset = '&subset='. htmlspecialchars($fontData->subset);
				}

				if ($t['tagidclass'] != '') {
					$css .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
					$css .= " " . $cssFontSize;
					$css .= " " . $cssAdd;
					$css .= '}'. "\n";

                    $cssOutput = '';

                    if ($this->plugin_number == 0) {
                        $cssOutput .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
                        $cssOutput .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
                        $this->setPluginNumber();
                    }

					//:regular,italic,bold,bolditalic
					$cssOutput .= ' <link href="https://fonts.googleapis.com/css2?family='.str_replace(' ', '+', $fontData->title). $variant . $subset .'"'
								.' rel="stylesheet" type="text/css" />'. "\n";
					$cssOutput .= '<style type="text/css">' . "\n" . $css . "\n" . '</style>'. "\n";
				} else {
					$cssOutput = '';
				}

				$bodySite   = $app->getBody();
				$bodySite   = str_replace('</head>', $cssOutput .'</head>', $bodySite);
				$app->setBody($bodySite);
			}
		 } else if (isset($fontData->format) && ($fontData->format == 'truetypevariable'
                 || $fontData->format == 'opentypevariable'
                 || $fontData->format == 'woff2variable'
                 || $fontData->format == 'woffvariable'

             )) {

             // VARIABLE TRUETYPE OR OPENTTYPE

             if(isset($fontData->title) && $fontData->title !='') {

				$fontData->title = htmlspecialchars($fontData->title);

				/*$variant = '';
				if(isset($fontData->variant) && $fontData->variant !='') {
					$variant = ':'. str_replace(' ', '', $fontData->variant);
					$variant = htmlspecialchars($variant);
				}

				$subset = '';
				if (isset($fontData->subset) && $fontData->subset != '') {
					$subset = '&subset='. htmlspecialchars($fontData->subset);
				}*/

                 $format = '';
				if (isset($fontData->format) && $fontData->format == 'woff2variable') {
				   $format = 'format("woff2-variations")';
				} else if (isset($fontData->format) && $fontData->format == 'woffvariable') {
				   $format = 'format("woff-variations")';
				} else if (isset($fontData->format) && $fontData->format == 'opentypevariable') {
				   $format = 'format("opentype-variations")';
				} else {
                   $format = 'format("truetype-variations")';
                }

                $linkFont       = URI::root().'media/com_phocafont/fonts/';
				$linkFontAbs    = JPATH_ROOT . '/media/com_phocafont/fonts/' ;

                if(isset($fontData->regular) && $fontData->regular !='') {
				   $css .= "\n" . '@font-face {';
				   $css.=' font-family: "'.$fontData->title.'";';
                   $css.=' font-weight: 1 999;';
                   $css.=' font-style: normal;';
				   $css.=' src: url("'.$linkFont.$fontData->regular.'") '.$format.';';
				   $css.=' }';

                   $css .= "\n" . '@font-face {';
				   $css.=' font-family: "'.$fontData->title.'";';
                   $css.=' font-weight: 1 999;';
                   $css.=' font-style: italic;';
				   $css.=' src: url("'.$linkFont.$fontData->regular.'") '.$format.';';
				   $css.=' }';

				}

				if ($t['tagidclass'] != '') {
					$css .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
					$css .= " ". $cssFontSize;
					$css .= " " . $cssAdd;
					$css .= '}';

                    $cssOutput = '';

                    /*if ($this->plugin_number == 0) {
                        $cssOutput .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
                        $cssOutput .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
                        $this->setPluginNumber();
                    }

					//:regular,italic,bold,bolditalic
					$cssOutput .= ' <link href="https://fonts.googleapis.com/css2?family='.str_replace(' ', '+', $fontData->title). $variant . $subset .'"'
								.' rel="stylesheet" type="text/css" />'. "\n";
                    */

					$cssOutput .= '<style type="text/css">' . "\n" . $css . "\n" . '</style>'. "\n";
				} else {
					$cssOutput = '';
				}

				$bodySite   = $app->getBody();
				$bodySite   = str_replace('</head>', $cssOutput .'</head>', $bodySite);
				$app->setBody($bodySite);
			}

         } else {

             // TRUETYPE OR OPENTYPE

			 if(isset($fontData->xmlfile) && $fontData->xmlfile !=''
			 && isset($fontData->title) && $fontData->title !='') {

				$linkFont       = URI::root().'/media/com_phocafont/fonts/';
				$linkFontAbs   = JPATH_ROOT . '/media/com_phocafont/fonts/' ;

				jimport( 'joomla.filesystem.file' );

				// Format
				$format = '';
				if (isset($fontData->format) && $fontData->format !='') {
				   $format = 'format("'.$fontData->format.'")';
				}

				// Regular
				if(isset($fontData->regular) && $fontData->regular !='') {
				   $cssR= "\n" . '@font-face {';
				   $cssR.=' font-family: "'.$fontData->title.'";';
				   $cssR.=' font-style: normal;';
				   $cssR.=' font-weight: normal;';
				   $cssR.=' font-stretch: normal;';
				   $cssR.=' font-stretch: normal;';
				   $cssR.=' src: url("'.$linkFont.$fontData->regular.'") '.$format.';';
				   $cssR.='}';
				   $css    .= $cssR;
				   // IE - - - - -
				   $fontData->regular_eot = str_replace ('ttf', 'eot', $fontData->regular);
				   $fontData->regular_eot = str_replace ('otf', 'eot', $fontData->regular_eot);
				   if (File::exists($linkFontAbs. $fontData->regular_eot)) {
					  $cssIe    .= $cssR;
				   }
				   // - - - - - -
				}
				// Bold
				if(isset($fontData->bold) && $fontData->bold !='') {
				   $cssB= "\n" . '@font-face {';
				   $cssB.=' font-family: "'.$fontData->title.'";';
				   $cssB.=' font-style: normal;';
				   $cssB.=' font-weight: bold;';
				   $cssB.=' font-stretch: normal;';
				   $cssB.=' src: url("'.$linkFont.$fontData->bold.'") '.$format.';';
				   $cssB.='}';
				   $css    .= $cssB;
				   // IE - - - - -
				   $fontData->bold_eot = str_replace ('ttf', 'eot', $fontData->bold);
				   $fontData->bold_eot = str_replace ('otf', 'eot', $fontData->bold_eot);
				   if (File::exists($linkFontAbs. $fontData->bold_eot)) {
					  $cssIe    .= $cssB;
				   }
				   // - - - - - -
				}
				// Italic
				if(isset($fontData->italic) && $fontData->italic !='') {
				   $cssI= "\n" . '@font-face {';
				   $cssI.=' font-family: "'.$fontData->title.'";';
				   $cssI.=' font-style: italic;';
				   $cssI.=' font-weight: normal;';
				   $cssI.=' font-stretch: normal;';
				   $cssI.=' src: url("'.$linkFont.$fontData->italic.'") '.$format.';';
				   $cssI.='}';
				   $css    .= $cssI;
				   // IE - - - - -
				   $fontData->italic_eot = str_replace ('ttf', 'eot', $fontData->italic);
				   $fontData->italic_eot = str_replace ('otf', 'eot', $fontData->italic_eot);
				   if (File::exists($linkFontAbs. $fontData->italic_eot)) {
					  $cssIe    .= $cssI;
				   }
				   // - - - - - -
				}
				// Bold Italic
				if(isset($fontData->bolditalic) && $fontData->bolditalic !='') {
				   $cssBI= "\n" . '@font-face {';
				   $cssBI.=' font-family: "'.$fontData->title.'";';
				   $cssBI.=' font-style: italic;';
				   $cssBI.=' font-weight: bold;';
				   $cssBI.=' font-stretch: normal;';
				   $cssBI.=' src: url("'.$linkFont.$fontData->italic.'") '.$format.';';
				   $cssBI.='}';
				   $css    .= $cssBI;
				   // IE - - - - -
				   $fontData->bolditalic_eot = str_replace ('ttf', 'eot', $fontData->bolditalic);
				   $fontData->bolditalic_eot = str_replace ('otf', 'eot', $fontData->bolditalic_eot);
				   if (File::exists($linkFontAbs. $fontData->bolditalic_eot)) {
					  $cssIe    .= $cssBI;
				   }
				   // - - - - - -
				}
				// Condensed
				if(isset($fontData->condensed) && $fontData->condensed !='') {
				   $cssC= "\n" . '@font-face {';
				   $cssC.=' font-family: "'.$fontData->title.'";';
				   $cssC.=' font-style: normal;';
				   $cssC.=' font-weight: normal;';
				   $cssC.=' font-stretch: condensed;';
				   $cssC.=' src: url("'.$linkFont.$fontData->condensed.'") '.$format.';';
				   $cssC.='}';
				   $css    .= $cssC;
				   // IE - - - - -
				   $fontData->condensed_eot = str_replace ('ttf', 'eot', $fontData->condensed);
				   $fontData->condensed_eot = str_replace ('otf', 'eot', $fontData->condensed_eot);
				   if (File::exists($linkFontAbs. $fontData->condensed_eot)) {
					  $cssIe    .= $cssC;
				   }
				   // - - - - - -
				}
				// Condensed Bold
				if(isset($fontData->condensedbold) && $fontData->condensedbold !='') {
				   $cssCB= "\n" . '@font-face {';
				   $cssCB.=' font-family: "'.$fontData->title.'";';
				   $cssCB.=' font-style: normal;';
				   $cssCB.=' font-weight: bold;';
				   $cssCB.=' font-stretch: condensed;';
				   $cssCB.=' src: url("'.$linkFont.$fontData->condensedbold.'") '.$format.';';
				   $cssCB.='}';
				   $css    .= $cssCB;
				   // IE - - - - -
				   $fontData->condensedbold_eot = str_replace ('ttf', 'eot', $fontData->condensedbold);
				   $fontData->condensedbold_eot = str_replace ('otf', 'eot', $fontData->condensedbold_eot);
				   if (File::exists($linkFontAbs. $fontData->condensedbold_eot)) {
					  $cssIe    .= $cssCB;
				   }
				   // - - - - - -
				}
				// Condensed Italic
				if(isset($fontData->condenseditalic) && $fontData->condenseditalic !='') {
				   $cssCI= "\n" . '@font-face {';
				   $cssCI.=' font-family: "'.$fontData->title.'";';
				   $cssCI.=' font-style: italic;';
				   $cssCI.=' font-weight: normal;';
				   $cssCI.=' font-stretch: condensed;';
				   $cssCI.=' src: url("'.$linkFont.$fontData->condenseditalic.'") '.$format.';';
				   $cssCI.='}';
				   $css    .= $cssCI;
				   // IE - - - - -
				   $fontData->condenseditalic_eot = str_replace ('ttf', 'eot', $fontData->condenseditalic);
				   $fontData->condenseditalic_eot = str_replace ('otf', 'eot', $fontData->condenseditalic_eot);
				   if (File::exists($linkFontAbs. $fontData->condenseditalic_eot)) {
					  $cssIe    .= $cssCI;
				   }
				   // - - - - - -
				}
				// Condensed Bold Italic
				if(isset($fontData->condensedbolditalic) && $fontData->condensedbolditalic !='') {
				   $cssCBI= "\n" . '@font-face {';
				   $cssCBI.=' font-family: "'.$fontData->title.'";';
				   $cssCBI.=' font-style: italic;';
				   $cssCBI.=' font-weight: bold;';
				   $cssCBI.=' font-stretch: condensed;';
				   $cssCBI.=' src: url("'.$linkFont.$fontData->condensedbolditalic.'") '.$format.';';
				   $cssCBI.='}';
				   $css   .= $cssCBI;
				   // IE - - - - -
				   $fontData->condensedbolditalic_eot = str_replace ('ttf', 'eot', $fontData->condensedbolditalic);
				   $fontData->condensedbolditalic_eot = str_replace ('otf', 'eot', $fontData->condensedbolditalic_eot);
				   if (File::exists($linkFontAbs. $fontData->condensedbolditalic_eot)) {
					  $cssIe    .= $cssCBI;
				   }
				   // - - - - - -
				}


			  /*  $alternative = '';
				if (isset($fontData->alternative) && $fontData->alternative !='') {
				   $alternative = ', '.$fontData->alternative;
				}

				$cssFontSize = '';
				if($t['fontsize'] !='') {
				   $cssFontSize = ' font-size: '.$t['fontsize'].';';
				}   */

				$css .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
				$css .= " " . $cssFontSize;
				$css .= " " . $cssAdd;
				$css .= '}';

				if ($cssIe != '') {
				   $cssIe .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
				   $cssIe .= " " . $cssFontSize;
				   $cssIe .= " " . $cssAdd;
				   $cssIe .= '}';
				}

				//$document->addStyleDeclaration($css);
				if ($cssIe != '') {

				   $cssIe   = str_replace('format("truetype")', '', $cssIe);
				   $cssIe   = str_replace('format("opentype")', '', $cssIe);
				   $cssIe = str_replace ('ttf', 'eot', $cssIe);
				   $cssIe = str_replace ('otf', 'eot', $cssIe);

				   $cssOutput = "\n" . '<!--[if IE]>' . "\n"
				   .'<style type="text/css">' . "\n" . $cssIe . "\n" . '</style>'
				   .'<![endif]-->'. "\n"
				   //.'<![if !IE]>'
				   .'<!--[if !IE]>-->' . "\n"
				   .'<style type="text/css">' . "\n" . $css . "\n" . '</style>'
				   //.'<![endif]>';
				   .'<!--<![endif]-->'."\n";
				} else {
				   $cssOutput = '<style type="text/css">' . "\n" . $css . "\n" . '</style>';
				}
			}
            $bodySite   = $app->getBody();
            $bodySite   = str_replace('</head>', $cssOutput .'</head>', $bodySite);
            $app->setBody($bodySite);
         }
      }
      return true;
   }
}
