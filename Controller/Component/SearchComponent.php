<?php
/**
*  AGPLv3 Chipotle Software (c) 2008-2012
*  Full Text Search Engine   -- Require  Postgresql 8.3 or higher or MySQL 5.0 or higher
*  @license GNU Affero General Public License V3
*  @author aarkerio
**/

/**
 *  Models to load to search in
 */
App::import('Model', 'Entry');
App::import('Model', 'News');
App::import('Model', 'Lesson');

class SearchComponent extends Component {
 
/* Make the new component available at $this->Math,
       as well as the standard $this->Session */
 public $components = array('Session', 'Auth');

 public  $controller  = True;
 private $_Keywords   = Null;
 private $lang        = '';
 public  $Data        = array();
 private $_Models     = '';


/**
 *  The initialize method is called before the controller’s beforeFilter method.
 *  @access public
*   @param Controller $controller A reference to the instantiating controller object
 *  @return void
 */
 public function initialize(Controller $controller) 
 {
	$this->request = $controller->request;
	$this->response = $controller->response;
	$this->_methods = $controller->methods;

  $this->_Models = array('Entry', 'News', 'Lesson'); # models to search in

  # lang is required to search in PostgreSQL
  if ($this->Auth->user('lang')):
      $this->lang = 'es';
  else:
      $this->lang = 'en';
  endif;
 }


/**
 * Build and execute search query
 * @access public
 * @param string $keywords
 */
 public function getRows($keywords)
 {
  $db = ConnectionManager::getDataSource('default');
 
  if (strlen($keywords) < 3):
      return Null;
  endif;
  
  $this->_Keywords = explode(' ', trim($keywords));  # convert to array
                                         
  if ( $db->config['datasource'] == 'Database/Mysql' ):
      $this->__searchMySQL();
  else:
      $this->__searchPgSQL();
  endif;

  $this->request->data['datasource'] =  $db->config['datasource']; # ? 

  return $this->request->data;
 }

/**
 * Build and execute search in PostgreSQL
 * @access private
 * @return void
 */
 private function __searchPgSQL()
 {
  # build terms array
  #die(debug($this->_Keywords));
  if ( count($this->_Keywords) === 1):
      $t = $this->_Keywords[0];
  else:
      $t = (string) '';
      for($i=0;count($this->_Keywords)<=$i;$i++):
          $t .= $this->_Keywords[$i];
	      if (count($this->_Keywords) < $i):
              $t .=  ' | ';   # "OR" postgresql search operator
	      endif;
     endfor;
  endif;
/*
Full text searching in PostgreSQL is based on the match operator @@, which returns true if a tsvector (document) matches a tsquery (query)
See: http://www.postgresql.org/docs/8.4/interactive/textsearch-intro.html#TEXTSEARCH-MATCHING
To present search results it is ideal to show a part of each document and how it is related to the query. Usually, search engines show fragments of the document with marked search terms. PostgreSQL provides a function ts_headline that implements this functionality.
*/
  foreach ($this->_Models as $model):
      switch ($model):
          case 'News':
              $q  = "SELECT id, title, ts_headline('karamelo_".$this->lang."', body, to_tsquery('karamelo_".$this->lang."','".$t."')), ";
              $q .= "rank FROM ( SELECT id, title, substr(body,0,260) as body, ts_rank_cd(to_tsvector('karamelo_".$this->lang."', body), ";
              $q .= "to_tsquery('karamelo_".$this->lang."','".$t."')) AS rank FROM news, to_tsquery('karamelo_".$this->lang."','".$t."') ";
              $q .= "query WHERE to_tsquery('karamelo_".$this->lang."','".$t."') @@ to_tsvector('karamelo_".$this->lang."', body) ";
              $q .= "ORDER BY rank DESC LIMIT 20) AS news";
              break;
          case 'Entry':
              $q  = "SELECT id, user_id, title,  ts_headline('karamelo_".$this->lang."', body, to_tsquery('karamelo_".$this->lang."','".$t."')) AS headline, ";
              $q .= "rank, username FROM (";
              $q .= "SELECT DISTINCT \"User\".\"username\",\"Entry\".\"id\",\"Entry\".\"user_id\",\"Entry\".\"title\",substr(\"Entry\".\"body\",0,260) AS body, ";
              $q .= "ts_rank_cd(to_tsvector('karamelo_".$this->lang."', body), to_tsquery('karamelo_es','".$t."')) AS rank ";
              $q .= "FROM \"entries\" AS \"Entry\", \"users\" AS \"User\" ";
              $q .= "WHERE to_tsquery('karamelo_".$this->lang."','".$t."') @@ to_tsvector('karamelo_".$this->lang."', \"Entry\".\"body\")";
              $q .= " AND \"User\".\"id\"=\"Entry\".\"user_id\" AND \"Entry\".\"status\"=1 ORDER BY rank DESC LIMIT 20) AS entries";
              break;
    case 'Lesson':
       $q  = "SELECT id, user_id, title, ts_headline('karamelo_".$this->lang."', body, to_tsquery('karamelo_".$this->lang."','".$t."')) AS headline, ";
       $q .= "rank, username FROM (";
       $q .= "SELECT DISTINCT \"User\".\"username\",\"Lesson\".\"id\",\"Lesson\".\"user_id\",\"Lesson\".\"title\",substr(\"Lesson\".\"body\",0,260) AS body, ";
       $q .= "ts_rank_cd(to_tsvector('karamelo_".$this->lang."', body), to_tsquery('karamelo_es','".$t."')) AS rank ";
       $q .= "FROM \"lessons\" AS \"Lesson\", \"users\" AS \"User\" ";
       $q .= "WHERE to_tsquery('karamelo_".$this->lang."','".$t."') @@ to_tsvector('karamelo_".$this->lang."', \"Lesson\".\"body\")";
       $q .= " AND \"Lesson\".\"public\"=1 AND \"Lesson\".\"status\"=1 AND \"User\".\"id\"=\"Lesson\".\"user_id\" ORDER BY rank DESC LIMIT 20) AS lessons";
       # echo $q.'</br >';
       /* case 'Glossary':
       $q  = "SELECT id, user_id, item, ts_headline('karamelo_".$this->lang."', definition, to_tsquery('karamelo_".$this->lang."','".$t."')) AS headline, ";
       $q .= "rank, username FROM (";
       $q .= "SELECT DISTINCT \"User\".\"username\",\"Lesson\".\"id\",\"Lesson\".\"user_id\",\"Lesson\".\"title\",substr(\"Lesson\".\"body\",0,260) AS body, ";
       $q .= "ts_rank_cd(to_tsvector('karamelo_".$this->lang."', body), to_tsquery('karamelo_es','".$t."')) AS rank ";
       $q .= "FROM \"lessons\" AS \"Lesson\", \"users\" AS \"User\" ";
       $q .= "WHERE to_tsquery('karamelo_".$this->lang."','".$t."') @@ to_tsvector('karamelo_".$this->lang."', \"Lesson\".\"body\")";
       $q .= " AND \"Lesson\".\"public\"=1 AND \"Lesson\".\"status\"=1 AND \"User\".\"id\"=\"Lesson\".\"user_id\" ORDER BY rank DESC LIMIT 20) AS lessons";
       # echo $q.'</br >';
       break;
   */
    endswitch;
    $Model =& new $model;
    $this->request->data[$model] = $Model->query($q);
   endforeach;
   #die(debug($this->request->data));
 }

/**
 * Build and execute search in MySQL
 * @access private
 * @return void
 */
 private function __searchMySQL()
 {
  $c = count($this->_Keywords);
  # build terms array
  if ( $c == 1):
      $t = trim($this->_Keywords[0]);
  else:
      $t = (string) '';
      for ($i=0;$i<$c;$i++):
          $t .= '+'.trim($this->_Keywords[$i]).' ';
     endfor;
  endif;

 #die(debug($t));

/*
 * Full text searching in MySQL
 */
  foreach ($this->_Models as $model):
    switch ($model):
        case 'News':
            $q  = "SELECT N.id,N.title, U.username,U.id FROM news as N,users AS U WHERE MATCH (N.title,N.body) AGAINST ('".$t."' IN BOOLEAN MODE) AND N.user_id=U.id ORDER BY N.title";
            break;
        case 'Entry':
            $q  = "SELECT N.id,N.title, U.username,U.id FROM entries as N,users AS U WHERE MATCH (N.title,N.body) AGAINST ('".$t."' IN BOOLEAN MODE) AND N.user_id=U.id ORDER BY N.title";
           break;
        case 'Lesson':
            $q  = "SELECT N.id,N.title, SUBSTRING(N.body,0,250) AS body, U.username,U.id FROM lessons as N,users AS U WHERE ";
            $q .= "MATCH (N.title,N.body) AGAINST ('".$t."' IN BOOLEAN MODE) ";
            $q .= "AND N.public=1 AND N.status=1 AND N.user_id=U.id ORDER BY N.title LIMIT 20";
            #die($q);
            /* case 'Glossary':
            $q  = "SELECT id, user_id, item, ts_headline('karamelo_".$this->lang."', definition, to_tsquery('karamelo_".$this->lang."','".$t."')) AS headline, ";
            $q .= "rank, username FROM (";
            $q .= "SELECT DISTINCT \"User\".\"username\",\"Lesson\".\"id\",\"Lesson\".\"user_id\",\"Lesson\".\"title\",substr(\"Lesson\".\"body\",0,260) AS body, ";
            $q .= "ts_rank_cd(to_tsvector('karamelo_".$this->lang."', body), to_tsquery('karamelo_es','".$t."')) AS rank ";
            $q .= "FROM \"lessons\" AS \"Lesson\", \"users\" AS \"User\" ";
            $q .= "WHERE to_tsquery('karamelo_".$this->lang."','".$t."') @@ to_tsvector('karamelo_".$this->lang."', \"Lesson\".\"body\")";
            $q .= " AND \"Lesson\".\"public\"=1 AND \"Lesson\".\"status\"=1 AND \"User\".\"id\"=\"Lesson\".\"user_id\" ORDER BY rank DESC LIMIT 20) AS lessons";
            # echo $q.'</br >';
            break;
            */
    endswitch;
    $Model =& new $model;
    $this->request->data[$model] = $Model->query($q);
   endforeach;
   #die(debug($this->request->data));
 }
}

# ? > EOF
