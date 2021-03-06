<?php
/**
 * Chipotle Software(c) 2012 GPLv3
 * File: APP/Test/Case/Controller/QuoteControllerTest.php
 */

App::import('Controller', 'Quotes');
App::import('Model', 'Quote');
App::uses('Controller', 'Controller');
App::uses('Model', 'Model');
App::uses('View', 'View');
App::uses('AclComponent', 'Controller/Component');


class QuotesControllerTest extends ControllerTestCase {

/*
 * Load fixed data
 * @var array
 */
    public $fixtures = array('app.aro', 'app.aco', 'app.group', 'app.aros_aco', 'app.quote', 'app.user');

 public $quotesMock = Null;

/**
 * setUp method
 *
 * @return void
 */
 public function setUp() 
 {
  parent::setUp();

  #$this->autoMock = False;  # Automatically mock controllers that are not mocked
 
 }

/**
  * testIndex method
  *
  * @return void
  */
 public function testShouldReturnOneQuote() 
 {                        
  $data   = array('user_id' => 1);
  $result = $this->testAction('/quotes/getOne/1', array('data' => $data, 'method' => 'get'));
  #die(debug($result));
  $this->assertTrue(is_array($result));
 }
 /**
  * testIndex method
  *
  * @return void
  */
 public function testSuccessfullSignIn() 
 {
   $this->markTestIncomplete('This test testAdminListing has not been implemented yet.');
   $result = $this->testAction('/admin/quotes/listing',array('return' => 'contents'));
   #die(debug($result));
   $this->assertRegexp('#<title>Karamelo::cPanel</title>#', $result);
 }

 /**
  * testIndex method
  * @access public
  * @return void
  */
 public function testAdminListing() 
 {
   # $this->markTestIncomplete('This test testAdminListing has not been implemented yet.');
   $Quotes = $this->generate('Quotes', array(
                                            'components' => array('Auth', 
                                                                  'Session')));  
   $Quotes->Auth
                 ->expects($this->once())
                 ->method('login')
                 ->will($this->returnValue(true)); 

     /*  $Quotes->Auth
      ->staticExpects($this->any())
      ->method('user')
      ->with('id')
      ->will($this->returnValue(1));*/

   $result = $this->testAction('/admin/quotes/listing', array('return' => 'contents'));
   #die(debug($result));
   $this->assertRegexp('#<title>Karamelo::cPanel</title>#', $result);
 }

/**
 *  Description
 *  @access public
 *  @return void
 *  @param integer $user_id
 * it "should return null (unauthorized) without a valid session"
 */
 public function testAddQuote() 
 {
  $this->markTestIncomplete('This test testAdminListing has not been implemented yet.');

  $data = array(
                'Quote' => array(
                                'quote'   => 'New Quote',
                                'author'  => 'My great author',
                                'user_id' => 1,
                                'id'      =>1000
                                )
                   );
  $results = $this->testAction('/admin/quotes/add', array('data' => $data, 'method' => 'post'));
  # some assertioons
  debug( $this->headers);
  $this->assertContains('/admin/quotes/listing', $this->headers['Location']);
  $this->assertEquals($results, True);
  #$this->assertRegexp('#<title>Karamelo::cPanel</title>#', $this->contents);
 }

/**
 *  Description
 *  @access public
 *  @return void
 *  @param integer $user_id
 */
  public function testListing() 
  {
 $this->markTestIncomplete('This test testAdminListing has not been implemented yet.');
    $result = $this->testAction('/admin/quotes/listing');
    #debug($result);
  }

 # Just clean the mess
  public function tearDown() 
  {
   parent::tearDown();
   # Clean up after we're done
   unset($this->Controller);
 }

 }

# ? > EOF
