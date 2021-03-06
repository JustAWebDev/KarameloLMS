<?php
/*
 * Chipotle Software(c) 2012
 * File: APP/Test/Fixture/ConfirmFixture.php
 */
class ConfirmFixture extends CakeTestFixture {
    
 /*
  * Optional Importing table information and records
  */
   #public $import = array('Model' => array('Confirm'), 'connection' => 'default');

    /* Optional. Set this property to load fixtures to a different test datasource */
    public $useDbConfig = 'test';

    public $fields = array(
                       'id'        => array('type' => 'integer', 'key' => 'primary'),
                       'quote'     => array('type' => 'string', 'length' => 255, 'null' => False),
                       'author'    => array('type' => 'string', 'length' => 255, 'null' => False),
                       'user_id'   => 'integer'
                       );

    public $records = array(
                            array('id'          => 1, 
                                  'quote'       => 'Confirm Confirm Confirm ',
                                  'author'      => 'Author 111 Author 111 Author 111 Author 111 ', 
                                  'user_id'     => 1
                                  ),

                            );
 }

# ? > EOF
