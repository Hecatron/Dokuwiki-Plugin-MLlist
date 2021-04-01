<?php
/**
 * Multiline List Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Adrian Sai-wah Tam <adrian.sw.tam@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

use dokuwiki\Parsing\Handler\Lists;

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_mllist extends DokuWiki_Syntax_Plugin {

  function getInfo(){
    return array(
      'author' => 'Adrian Sai-wah Tam',
      'email'  => 'adrian.sw.tam@gmail.com',
      'date'   => '2007-06-06',
      'name'   => 'Multiline list plugin',
      'desc'   => 'Allows a list item to break into multiple lines with indentation on non-bullet lines',
      'url'    => 'http://aipl.ie.cuhk.edu.hk/~adrian/doku.php/software/mllist'
    );
  }

  function getType(){ return 'container'; }
  function getPType(){ return 'block'; }
  function getSort(){ return 9; }

  function getAllowedTypes(){
    return array('formatting', 'substition', 'disabled', 'protected');
  }

  function connectTo($mode){
    $this->Lexer->addEntryPattern('\n {2,}[\-\*]',$mode,'plugin_mllist');
    $this->Lexer->addEntryPattern('\n\t{1,}[\-\*]',$mode,'plugin_mllist');
    $this->Lexer->addPattern('\n {2,}[\-\*]','plugin_mllist');
    $this->Lexer->addPattern('\n\t{1,}[\-\*]','plugin_mllist');
    // Continuation lines need at least three spaces for indentation
    $this->Lexer->addPattern('\n {2,}(?=\s)','plugin_mllist');
    $this->Lexer->addPattern('\n\t{1,}(?=\s)','plugin_mllist');
  }

  function postConnect(){
    $this->Lexer->addExitPattern('\n','plugin_mllist');
  }

  function handle($match, $state, $pos, Doku_Handler $handler){
    switch ($state){
      case DOKU_LEXER_ENTER:
        $ReWriter = new Lists($handler->getCallWriter());
        $handler->setCallWriter($ReWriter);
        $handler->_addCall('list_open', array($match), $pos);
        break;
      case DOKU_LEXER_EXIT:
        $handler->_addCall('list_close', array(), $pos);
        $handler->getCallWriter()->process();
        $ReWriter = & $handler->getCallWriter();
        $handler->setCallWriter($ReWriter->getCallWriter());
        break;
      case DOKU_LEXER_MATCHED:
        if (preg_match("/^\s+$/",$match)) break;
            // Captures the continuation case
        $handler->_addCall('list_item', array($match), $pos);
        break;
      case DOKU_LEXER_UNMATCHED:
        $handler->_addCall('cdata', array($match), $pos);
        break;
    }
    return true;
  }

  function render($mode, Doku_Renderer $renderer, $data){
    return true;
  }
}
