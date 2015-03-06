<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.application.component.helper' );

class  plgSystemPhocaFont extends JPlugin
{
   
   public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

   function onAfterRender() {

		$document   = JFactory::getDocument();
		$doctype	= $document->getType();
		$db			= JFactory::getDBO();
		$app       	= JFactory::getApplication();
		$component	= 'com_phocafont';
		$t		= array();

		if (!JComponentHelper::isEnabled($component)) {
			echo JText::_('Phoca Font Plugin requires Phoca Font Component');
			return true;
		}
		  
		if($app->getName() != 'site') {
			return true;
		}
		  
		if ( $doctype !== 'html' ){
			return true;
		}
		  
		//$table          =& JTable::getInstance('component');
		//$table->loadByOption( $component );
		//$paramsC       = new JParameter( $table->params );
		
		$component			= 'com_phocafont';
		$paramsC			= JComponentHelper::getParams($component) ;

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

      // Itemid check - - - - -
      $ItemId      = JRequest::getVar('Itemid', 1, 'int');
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
		   $cssFontSize = '       font-size: '.$t['fontsize'].';'."\n";
		} 
		
		// Additional 
		$cssAdd = '';
		if($t['addcss'] !='') {
		   $cssAdd = '       '.strip_tags($t['addcss'])."\n";
		}  

		 
		 
		 // External Font
         if (isset($fontData->format) && $fontData->format == 'externalfonttype') {
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
					$css .= "\n" . $cssFontSize;
					$css .= "\n" . $cssAdd;
					$css .=   '}'. "\n";
					
					//:regular,italic,bold,bolditalic
					$cssOutput = ' <link href="http://fonts.googleapis.com/css?family='.str_replace(' ', '+', $fontData->title). $variant . $subset .'"'
								.' rel="stylesheet" type="text/css" />'. "\n";
					$cssOutput .= '<style type="text/css">' . "\n" . $css . "\n" . '</style>'. "\n";
				} else {
					$cssOutput = '';
				}
				
				$bodySite   = JResponse::getBody();
				$bodySite   = str_replace('</head>', $cssOutput .'</head>', $bodySite);
				JResponse::setBody($bodySite);
			}
		 } else {
		 
			 if(isset($fontData->xmlfile) && $fontData->xmlfile !=''
			 && isset($fontData->title) && $fontData->title !='') {
				
				$linkFont       = JURI::base(true).'/components/com_phocafont/fonts/';
				$linkFontAbs   = JPATH_ROOT . '/components/com_phocafont/fonts/' ;
				
				jimport( 'joomla.filesystem.file' );

				// Format
				$format = '';
				if (isset($fontData->format) && $fontData->format !='') {
				   $format = 'format("'.$fontData->format.'")';
				}
				
				// Regular
				if(isset($fontData->regular) && $fontData->regular !='') {
				   $cssR='@font-face {'."\n";
				   $cssR.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssR.='       font-style: normal;'."\n";
				   $cssR.='       font-weight: normal;'."\n";
				   $cssR.='       font-stretch: normal;'."\n";
				   $cssR.='       font-stretch: normal;'."\n";
				   $cssR.='       src: url("'.$linkFont.$fontData->regular.'") '.$format.';'."\n";
				   $cssR.='   }';
				   $css    .= $cssR;
				   // IE - - - - -
				   $fontData->regular_eot = str_replace ('ttf', 'eot', $fontData->regular);
				   $fontData->regular_eot = str_replace ('otf', 'eot', $fontData->regular_eot);
				   if (JFile::exists($linkFontAbs. $fontData->regular_eot)) {
					  $cssIe    .= $cssR;
				   }
				   // - - - - - -
				}
				// Bold
				if(isset($fontData->bold) && $fontData->bold !='') {
				   $cssB='@font-face {'."\n";
				   $cssB.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssB.='       font-style: normal;'."\n";
				   $cssB.='       font-weight: bold;'."\n";
				   $cssB.='       font-stretch: normal;'."\n";
				   $cssB.='       src: url("'.$linkFont.$fontData->bold.'") '.$format.';'."\n";
				   $cssB.='   }';
				   $css    .= $cssB;
				   // IE - - - - -
				   $fontData->bold_eot = str_replace ('ttf', 'eot', $fontData->bold);
				   $fontData->bold_eot = str_replace ('otf', 'eot', $fontData->bold_eot);
				   if (JFile::exists($linkFontAbs. $fontData->bold_eot)) {
					  $cssIe    .= $cssB;
				   }
				   // - - - - - -
				}
				// Italic
				if(isset($fontData->italic) && $fontData->italic !='') {
				   $cssI='@font-face {'."\n";
				   $cssI.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssI.='       font-style: italic;'."\n";
				   $cssI.='       font-weight: normal;'."\n";
				   $cssI.='       font-stretch: normal;'."\n";
				   $cssI.='       src: url("'.$linkFont.$fontData->italic.'") '.$format.';'."\n";
				   $cssI.='   }';
				   $css    .= $cssI;
				   // IE - - - - -
				   $fontData->italic_eot = str_replace ('ttf', 'eot', $fontData->italic);
				   $fontData->italic_eot = str_replace ('otf', 'eot', $fontData->italic_eot);
				   if (JFile::exists($linkFontAbs. $fontData->italic_eot)) {
					  $cssIe    .= $cssI;
				   }
				   // - - - - - -
				}
				// Bold Italic
				if(isset($fontData->bolditalic) && $fontData->bolditalic !='') {
				   $cssBI='@font-face {'."\n";
				   $cssBI.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssBI.='       font-style: italic;'."\n";
				   $cssBI.='       font-weight: bold;'."\n";
				   $cssBI.='       font-stretch: normal;'."\n";
				   $cssBI.='       src: url("'.$linkFont.$fontData->italic.'") '.$format.';'."\n";
				   $cssBI.='   }';
				   $css    .= $cssBI;
				   // IE - - - - -
				   $fontData->bolditalic_eot = str_replace ('ttf', 'eot', $fontData->bolditalic);
				   $fontData->bolditalic_eot = str_replace ('otf', 'eot', $fontData->bolditalic_eot);
				   if (JFile::exists($linkFontAbs. $fontData->bolditalic_eot)) {
					  $cssIe    .= $cssBI;
				   }
				   // - - - - - -
				}
				// Condensed
				if(isset($fontData->condensed) && $fontData->condensed !='') {
				   $cssC='@font-face {'."\n";
				   $cssC.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssC.='       font-style: normal;'."\n";
				   $cssC.='       font-weight: normal;'."\n";
				   $cssC.='       font-stretch: condensed;'."\n";
				   $cssC.='       src: url("'.$linkFont.$fontData->condensed.'") '.$format.';'."\n";
				   $cssC.='   }';
				   $css    .= $cssC;
				   // IE - - - - -
				   $fontData->condensed_eot = str_replace ('ttf', 'eot', $fontData->condensed);
				   $fontData->condensed_eot = str_replace ('otf', 'eot', $fontData->condensed_eot);
				   if (JFile::exists($linkFontAbs. $fontData->condensed_eot)) {
					  $cssIe    .= $cssC;
				   }
				   // - - - - - -
				}
				// Condensed Bold
				if(isset($fontData->condensedbold) && $fontData->condensedbold !='') {
				   $cssCB='@font-face {'."\n";
				   $cssCB.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssCB.='       font-style: normal;'."\n";
				   $cssCB.='       font-weight: bold;'."\n";
				   $cssCB.='       font-stretch: condensed;'."\n";
				   $cssCB.='       src: url("'.$linkFont.$fontData->condensedbold.'") '.$format.';'."\n";
				   $cssCB.='   }';
				   $css    .= $cssCB;
				   // IE - - - - -
				   $fontData->condensedbold_eot = str_replace ('ttf', 'eot', $fontData->condensedbold);
				   $fontData->condensedbold_eot = str_replace ('otf', 'eot', $fontData->condensedbold_eot);
				   if (JFile::exists($linkFontAbs. $fontData->condensedbold_eot)) {
					  $cssIe    .= $cssCB;
				   }
				   // - - - - - -
				}
				// Condensed Italic
				if(isset($fontData->condenseditalic) && $fontData->condenseditalic !='') {
				   $cssCI='@font-face {'."\n";
				   $cssCI.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssCI.='       font-style: italic;'."\n";
				   $cssCI.='       font-weight: normal;'."\n";
				   $cssCI.='       font-stretch: condensed;'."\n";
				   $cssCI.='       src: url("'.$linkFont.$fontData->condenseditalic.'") '.$format.';'."\n";
				   $cssCI.='   }';
				   $css    .= $cssCI;
				   // IE - - - - -
				   $fontData->condenseditalic_eot = str_replace ('ttf', 'eot', $fontData->condenseditalic);
				   $fontData->condenseditalic_eot = str_replace ('otf', 'eot', $fontData->condenseditalic_eot);
				   if (JFile::exists($linkFontAbs. $fontData->condenseditalic_eot)) {
					  $cssIe    .= $cssCI;
				   }
				   // - - - - - -
				}
				// Condensed Bold Italic
				if(isset($fontData->condensedbolditalic) && $fontData->condensedbolditalic !='') {
				   $cssCBI='@font-face {'."\n";
				   $cssCBI.='       font-family: "'.$fontData->title.'";'."\n";
				   $cssCBI.='       font-style: italic;'."\n";
				   $cssCBI.='       font-weight: bold;'."\n";
				   $cssCBI.='       font-stretch: condensed;'."\n";
				   $cssCBI.='       src: url("'.$linkFont.$fontData->condensedbolditalic.'") '.$format.';'."\n";
				   $cssCBI.='   }';
				   $css   .= $cssCBI;
				   // IE - - - - -
				   $fontData->condensedbolditalic_eot = str_replace ('ttf', 'eot', $fontData->condensedbolditalic);
				   $fontData->condensedbolditalic_eot = str_replace ('otf', 'eot', $fontData->condensedbolditalic_eot);
				   if (JFile::exists($linkFontAbs. $fontData->condensedbolditalic_eot)) {
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
				   $cssFontSize = '       font-size: '.$t['fontsize'].';'."\n";
				}   */
				
				$css .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
				$css .= "\n" . $cssFontSize;
				$css .= "\n" . $cssAdd;
				$css .=   '}';
				
				if ($cssIe != '') {
				   $cssIe .= "\n" . $t['tagidclass'] .' { font-family: "'.$fontData->title.'"'.$alternative.';';
				   $cssIe .= "\n" . $cssFontSize;
				   $cssIe .= "\n" . $cssAdd;
				   $cssIe .=   '}';
				}
			 
				//$document->addStyleDeclaration($css);
				if ($cssIe != '') {
				
				   $cssIe   = str_replace('format("truetype")', '', $cssIe);
				   $cssIe   = str_replace('format("opentype")', '', $cssIe);
				   $cssIe = str_replace ('ttf', 'eot', $cssIe);
				   $cssIe = str_replace ('otf', 'eot', $cssIe);
				
				   $cssOutput = "\n\n" . '<!--[if IE]>' . "\n"
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
            $bodySite   = JResponse::getBody();
            $bodySite   = str_replace('</head>', $cssOutput .'</head>', $bodySite);
            JResponse::setBody($bodySite);
         }
      }
      return true;
   }
}