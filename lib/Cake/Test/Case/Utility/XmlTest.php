<?php
/**
 * XmlTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       Cake.Test.Case.Utility
 * @since         CakePHP(tm) v 1.2.0.5432
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Xml', 'Utility');
App::uses('CakeTestModel', 'TestSuite/Fixture');

/**
 * Article class
 *
 * @package       Cake.Test.Case.Utility
 */
class XmlArticle extends CakeTestModel {

/**
 * name property
 *
 * @var string 'Article'
 */
	public $name = 'Article';

/**
 * belongsTo property
 *
 * @var array
 */
	public $belongsTo = array(
		'XmlUser' => array(
			'className' => 'XmlArticle',
			'foreignKey' => 'user_id'
		)
	);
}

/**
 * User class
 *
 * @package       Cake.Test.Case.Utility
 */
class XmlUser extends CakeTestModel {

/**
 * name property
 *
 * @var string 'User'
 */
	public $name = 'User';

/**
 * hasMany property
 *
 * @var array
 */
	public $hasMany = array('Article');
}

/**
 * XmlTest class
 *
 * @package       Cake.Test.Case.Utility
 */
class XmlTest extends CakeTestCase {

/**
 * autoFixtures property
 *
 * @var bool false
 */
	public $autoFixtures = false;

/**
 * fixtures property
 * @var array
 */
	public $fixtures = array(
		'core.article', 'core.user'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_appEncoding = Configure::read('App.encoding');
		Configure::write('App.encoding', 'UTF-8');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		Configure::write('App.encoding', $this->_appEncoding);
	}

/**
 * testBuild method
 *
 * @return void
 */
	public function testBuild() {
		$xml = '<tag>value</tag>';
		$obj = Xml::build($xml);
		$this->assertTrue($obj instanceof SimpleXMLElement);
		$this->assertEquals((string)$obj->getName(), 'tag');
		$this->assertEquals((string)$obj, 'value');

		$xml = '<?xml version="1.0" encoding="UTF-8"?><tag>value</tag>';
		$this->assertEquals($obj, Xml::build($xml));

		$obj = Xml::build($xml, array('return' => 'domdocument'));
		$this->assertTrue($obj instanceof DOMDocument);
		$this->assertEquals($obj->firstChild->nodeName, 'tag');
		$this->assertEquals($obj->firstChild->nodeValue, 'value');

		$xml = CAKE . 'Test' . DS . 'Fixture' . DS . 'sample.xml';
		$obj = Xml::build($xml);
		$this->assertEquals($obj->getName(), 'tags');
		$this->assertEquals(count($obj), 2);

		$this->assertEquals(Xml::build($xml), Xml::build(file_get_contents($xml)));

		$obj = Xml::build($xml, array('return' => 'domdocument'));
		$this->assertEquals($obj->firstChild->nodeName, 'tags');

		$this->assertEquals(Xml::build($xml, array('return' => 'domdocument')), Xml::build(file_get_contents($xml), array('return' => 'domdocument')));
		$this->assertEquals(Xml::build($xml, array('return' => 'simplexml')), Xml::build($xml, 'simplexml'));

		$xml = array('tag' => 'value');
		$obj = Xml::build($xml);
		$this->assertEquals($obj->getName(), 'tag');
		$this->assertEquals((string)$obj, 'value');

		$obj = Xml::build($xml, array('return' => 'domdocument'));
		$this->assertEquals($obj->firstChild->nodeName, 'tag');
		$this->assertEquals($obj->firstChild->nodeValue, 'value');

		$obj = Xml::build($xml, array('return' => 'domdocument', 'encoding' => null));
		$this->assertNotRegExp('/encoding/', $obj->saveXML());
	}

/**
 * data provider function for testBuildInvalidData
 *
 * @return array
 */
	public static function invalidDataProvider() {
		return array(
			array(null),
			array(false),
			array(''),
		);
	}

/**
 * testBuildInvalidData
 *
 * @dataProvider invalidDataProvider
 * @expectedException XmlException
 * return void
 */
	public function testBuildInvalidData($value) {
		Xml::build($value);
	}

/**
 * test build with a single empty tag
 *
 * return void
 */
	public function testBuildEmptyTag() {
		try {
			Xml::build('<tag>');
			$this->fail('No exception');
		} catch (Exception $e) {
			$this->assertTrue(true, 'An exception was raised');
		}
	}

/**
 * testFromArray method
 *
 * @return void
 */
	public function testFromArray() {
		$xml = array('tag' => 'value');
		$obj = Xml::fromArray($xml);
		$this->assertEquals($obj->getName(), 'tag');
		$this->assertEquals((string)$obj, 'value');

		$xml = array('tag' => null);
		$obj = Xml::fromArray($xml);
		$this->assertEquals($obj->getName(), 'tag');
		$this->assertEquals((string)$obj, '');

		$xml = array('tag' => array('@' => 'value'));
		$obj = Xml::fromArray($xml);
		$this->assertEquals($obj->getName(), 'tag');
		$this->assertEquals((string)$obj, 'value');

		$xml = array(
			'tags' => array(
				'tag' => array(
					array(
						'id' => '1',
						'name' => 'defect'
					),
					array(
						'id' => '2',
						'name' => 'enhancement'
					)
				)
			)
		);
		$obj = Xml::fromArray($xml, 'attributes');
		$this->assertTrue($obj instanceof SimpleXMLElement);
		$this->assertEquals($obj->getName(), 'tags');
		$this->assertEquals(count($obj), 2);
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?><tags><tag id="1" name="defect"/><tag id="2" name="enhancement"/></tags>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $xmlText);

		$obj = Xml::fromArray($xml);
		$this->assertTrue($obj instanceof SimpleXMLElement);
		$this->assertEquals($obj->getName(), 'tags');
		$this->assertEquals(count($obj), 2);
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?><tags><tag><id>1</id><name>defect</name></tag><tag><id>2</id><name>enhancement</name></tag></tags>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $xmlText);

		$xml = array(
			'tags' => array(
			)
		);
		$obj = Xml::fromArray($xml);
		$this->assertEquals($obj->getName(), 'tags');
		$this->assertEquals((string)$obj, '');

		$xml = array(
			'tags' => array(
				'bool' => true,
				'int' => 1,
				'float' => 10.2,
				'string' => 'ok',
				'null' => null,
				'array' => array()
			)
		);
		$obj = Xml::fromArray($xml, 'tags');
		$this->assertEquals(count($obj), 6);
		$this->assertSame((string)$obj->bool, '1');
		$this->assertSame((string)$obj->int, '1');
		$this->assertSame((string)$obj->float, '10.2');
		$this->assertSame((string)$obj->string, 'ok');
		$this->assertSame((string)$obj->null, '');
		$this->assertSame((string)$obj->array, '');

		$xml = array(
			'tags' => array(
				'tag' => array(
					array(
						'@id' => '1',
						'name' => 'defect'
					),
					array(
						'@id' => '2',
						'name' => 'enhancement'
					)
				)
			)
		);
		$obj = Xml::fromArray($xml, 'tags');
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?><tags><tag id="1"><name>defect</name></tag><tag id="2"><name>enhancement</name></tag></tags>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $xmlText);

		$xml = array(
			'tags' => array(
				'tag' => array(
					array(
						'@id' => '1',
						'name' => 'defect',
						'@' => 'Tag 1'
					),
					array(
						'@id' => '2',
						'name' => 'enhancement'
					),
				),
				'@' => 'All tags'
			)
		);
		$obj = Xml::fromArray($xml, 'tags');
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?><tags>All tags<tag id="1">Tag 1<name>defect</name></tag><tag id="2"><name>enhancement</name></tag></tags>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $xmlText);

		$xml = array(
			'tags' => array(
				'tag' => array(
					'id' => 1,
					'@' => 'defect'
				)
			)
		);
		$obj = Xml::fromArray($xml, 'attributes');
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?><tags><tag id="1">defect</tag></tags>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $xmlText);
	}

/**
 * data provider for fromArray() failures
 *
 * @return array
 */
	public static function invalidArrayDataProvider() {
		return array(
			array(''),
			array(null),
			array(false),
			array(array()),
			array(array('numeric key as root')),
			array(array('item1' => '', 'item2' => '')),
			array(array('items' => array('item1', 'item2'))),
			array(array(
				'tags' => array(
					'tag' => array(
						array(
							array(
								'string'
							)
						)
					)
				)
			)),
			array(array(
				'tags' => array(
					'@tag' => array(
						array(
							'@id' => '1',
							'name' => 'defect'
						),
						array(
							'@id' => '2',
							'name' => 'enhancement'
						)
					)
				)
			)),
			array(new DateTime())
		);
	}

/**
 * testFromArrayFail method
 *
 * @dataProvider invalidArrayDataProvider
 */
	public function testFromArrayFail($value) {
		try {
			Xml::fromArray($value);
			$this->fail('No exception.');
		} catch (Exception $e) {
			$this->assertTrue(true, 'Caught exception.');
		}
	}

/**
 * testToArray method
 *
 * @return void
 */
	public function testToArray() {
		$xml = '<tag>name</tag>';
		$obj = Xml::build($xml);
		$this->assertEquals(Xml::toArray($obj), array('tag' => 'name'));

		$xml = CAKE . 'Test' . DS . 'Fixture' . DS . 'sample.xml';
		$obj = Xml::build($xml);
		$expected = array(
			'tags' => array(
				'tag' => array(
					array(
						'@id' => '1',
						'name' => 'defect'
					),
					array(
						'@id' => '2',
						'name' => 'enhancement'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray($obj), $expected);

		$array = array(
			'tags' => array(
				'tag' => array(
					array(
						'id' => '1',
						'name' => 'defect'
					),
					array(
						'id' => '2',
						'name' => 'enhancement'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, 'tags')), $array);

		$expected = array(
			'tags' => array(
				'tag' => array(
					array(
						'@id' => '1',
						'@name' => 'defect'
					),
					array(
						'@id' => '2',
						'@name' => 'enhancement'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, 'attributes')), $expected);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, array('return' => 'domdocument', 'format' => 'attributes'))), $expected);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array)), $array);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, array('return' => 'domdocument'))), $array);

		$array = array(
			'tags' => array(
				'tag' => array(
					'id' => '1',
					'posts' => array(
						array('id' => '1'),
						array('id' => '2')
					)
				),
				'tagOther' => array(
					'subtag' => array(
						'id' => '1'
					)
				)
			)
		);
		$expected = array(
			'tags' => array(
				'tag' => array(
					'@id' => '1',
					'posts' => array(
						array('@id' => '1'),
						array('@id' => '2')
					)
				),
				'tagOther' => array(
					'subtag' => array(
						'@id' => '1'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, 'attributes')), $expected);
		$this->assertEquals(Xml::toArray(Xml::fromArray($array, array('format' => 'attributes', 'return' => 'domdocument'))), $expected);

		$xml = '<root>';
		$xml .= '<tag id="1">defect</tag>';
		$xml .= '</root>';
		$obj = Xml::build($xml);

		$expected = array(
			'root' => array(
				'tag' => array(
					'@id' => 1,
					'@' => 'defect'
				)
			)
		);
		$this->assertEquals(Xml::toArray($obj), $expected);

		$xml = '<root>';
		$xml .= '<table xmlns="http://www.w3.org/TR/html4/"><tr><td>Apples</td><td>Bananas</td></tr></table>';
		$xml .= '<table xmlns="http://www.cakephp.org"><name>CakePHP</name><license>MIT</license></table>';
		$xml .= '<table>The book is on the table.</table>';
		$xml .= '</root>';
		$obj = Xml::build($xml);

		$expected = array(
			'root' => array(
				'table' => array(
					array('tr' => array('td' => array('Apples', 'Bananas'))),
					array('name' => 'CakePHP', 'license' => 'MIT'),
					'The book is on the table.'
				)
			)
		);
		$this->assertEquals(Xml::toArray($obj), $expected);

		$xml = '<root xmlns:cake="http://www.cakephp.org/">';
		$xml .= '<tag>defect</tag>';
		$xml .= '<cake:bug>1</cake:bug>';
		$xml .= '</root>';
		$obj = Xml::build($xml);

		$expected = array(
			'root' => array(
				'tag' => 'defect',
				'cake:bug' => 1
			)
		);
		$this->assertEquals(Xml::toArray($obj), $expected);
	}

/**
 * testRss
 *
 * @return void
 */
	public function testRss() {
		$rss = file_get_contents(CAKE . 'Test' . DS . 'Fixture' . DS . 'rss.xml');
		$rssAsArray = Xml::toArray(Xml::build($rss));
		$this->assertEquals($rssAsArray['rss']['@version'], '2.0');
		$this->assertEquals(count($rssAsArray['rss']['channel']['item']), 2);

		$atomLink = array('@href' => 'http://bakery.cakephp.org/articles/rss', '@rel' => 'self', '@type' => 'application/rss+xml');
		$this->assertEquals($rssAsArray['rss']['channel']['atom:link'], $atomLink);
		$this->assertEquals($rssAsArray['rss']['channel']['link'], 'http://bakery.cakephp.org/');

		$expected = array(
			'title' => 'Alertpay automated sales via IPN',
			'link' => 'http://bakery.cakephp.org/articles/view/alertpay-automated-sales-via-ipn',
			'description' => 'I\'m going to show you how I implemented a payment module via the Alertpay payment processor.',
			'pubDate' => 'Tue, 31 Aug 2010 01:42:00 -0500',
			'guid' => 'http://bakery.cakephp.org/articles/view/alertpay-automated-sales-via-ipn'
		);
		$this->assertSame($rssAsArray['rss']['channel']['item'][1], $expected);

		$rss = array(
			'rss' => array(
				'xmlns:atom' => 'http://www.w3.org/2005/Atom',
				'@version' => '2.0',
				'channel' => array(
					'atom:link' => array(
						'@href' => 'http://bakery.cakephp.org/articles/rss',
						'@rel' => 'self',
						'@type' => 'application/rss+xml'
					),
					'title' => 'The Bakery: ',
					'link' => 'http://bakery.cakephp.org/',
					'description' => 'Recent  Articles at The Bakery.',
					'pubDate' => 'Sun, 12 Sep 2010 04:18:26 -0500',
					'item' => array(
						array(
							'title' => 'CakePHP 1.3.4 released',
							'link' => 'http://bakery.cakephp.org/articles/view/cakephp-1-3-4-released'
						),
						array(
							'title' => 'Wizard Component 1.2 Tutorial',
							'link' => 'http://bakery.cakephp.org/articles/view/wizard-component-1-2-tutorial'
						)
					)
				)
			)
		);
		$rssAsSimpleXML = Xml::fromArray($rss);
		$xmlText = '<' . '?xml version="1.0" encoding="UTF-8"?>';
		$xmlText .= '<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">';
		$xmlText .= '<channel>';
		$xmlText .= '<atom:link href="http://bakery.cakephp.org/articles/rss" rel="self" type="application/rss+xml"/>';
		$xmlText .= '<title>The Bakery: </title>';
		$xmlText .= '<link>http://bakery.cakephp.org/</link>';
		$xmlText .= '<description>Recent  Articles at The Bakery.</description>';
		$xmlText .= '<pubDate>Sun, 12 Sep 2010 04:18:26 -0500</pubDate>';
		$xmlText .= '<item><title>CakePHP 1.3.4 released</title><link>http://bakery.cakephp.org/articles/view/cakephp-1-3-4-released</link></item>';
		$xmlText .= '<item><title>Wizard Component 1.2 Tutorial</title><link>http://bakery.cakephp.org/articles/view/wizard-component-1-2-tutorial</link></item>';
		$xmlText .= '</channel></rss>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $rssAsSimpleXML->asXML()), $xmlText);
	}

/**
 * testXmlRpc
 *
 * @return void
 */
	public function testXmlRpc() {
		$xml = Xml::build('<methodCall><methodName>test</methodName><params /></methodCall>');
		$expected = array(
			'methodCall' => array(
				'methodName' => 'test',
				'params' => ''
			)
		);
		$this->assertSame(Xml::toArray($xml), $expected);

		$xml = Xml::build('<methodCall><methodName>test</methodName><params><param><value><array><data><value><int>12</int></value><value><string>Egypt</string></value><value><boolean>0</boolean></value><value><int>-31</int></value></data></array></value></param></params></methodCall>');
		$expected = array(
			'methodCall' => array(
				'methodName' => 'test',
				'params' => array(
					'param' => array(
						'value' => array(
							'array' => array(
								'data' => array(
									'value' => array(
										array('int' => '12'),
										array('string' => 'Egypt'),
										array('boolean' => '0'),
										array('int' => '-31')
									)
								)
							)
						)
					)
				)
			)
		);
		$this->assertSame(Xml::toArray($xml), $expected);

		$xmlText = '<?xml version="1.0" encoding="UTF-8"?><methodResponse><params><param><value><array><data><value><int>1</int></value><value><string>testing</string></value></data></array></value></param></params></methodResponse>';
		$xml = Xml::build($xmlText);
		$expected = array(
			'methodResponse' => array(
				'params' => array(
					'param' => array(
						'value' => array(
							'array' => array(
								'data' => array(
									'value' => array(
										array('int' => '1'),
										array('string' => 'testing')
									)
								)
							)
						)
					)
				)
			)
		);
		$this->assertSame(Xml::toArray($xml), $expected);

		$xml = Xml::fromArray($expected, 'tags');
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xml->asXML()), $xmlText);
	}

/**
 * testSoap
 *
 * @return void
 */
	public function testSoap() {
		$xmlRequest = Xml::build(CAKE . 'Test' . DS . 'Fixture' . DS . 'soap_request.xml');
		$expected = array(
			'Envelope' => array(
				'@soap:encodingStyle' => 'http://www.w3.org/2001/12/soap-encoding',
				'soap:Body' => array(
					'm:GetStockPrice' => array(
						'm:StockName' => 'IBM'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray($xmlRequest), $expected);

		$xmlResponse = Xml::build(CAKE . 'Test' . DS . 'Fixture' . DS . 'soap_response.xml');
		$expected = array(
			'Envelope' => array(
				'@soap:encodingStyle' => 'http://www.w3.org/2001/12/soap-encoding',
				'soap:Body' => array(
					'm:GetStockPriceResponse' => array(
						'm:Price' => '34.5'
					)
				)
			)
		);
		$this->assertEquals(Xml::toArray($xmlResponse), $expected);

		$xml = array(
			'soap:Envelope' => array(
				'xmlns:soap' => 'http://www.w3.org/2001/12/soap-envelope',
				'@soap:encodingStyle' => 'http://www.w3.org/2001/12/soap-encoding',
				'soap:Body' => array(
					'xmlns:m' => 'http://www.example.org/stock',
					'm:GetStockPrice' => array(
						'm:StockName' => 'IBM'
					)
				)
			)
		);
		$xmlRequest = Xml::fromArray($xml, array('encoding' => null));
		$xmlText = '<' . '?xml version="1.0"?>';
		$xmlText .= '<soap:Envelope xmlns:soap="http://www.w3.org/2001/12/soap-envelope" soap:encodingStyle="http://www.w3.org/2001/12/soap-encoding">';
		$xmlText .= '<soap:Body xmlns:m="http://www.example.org/stock">';
		$xmlText .= '<m:GetStockPrice><m:StockName>IBM</m:StockName></m:GetStockPrice>';
		$xmlText .= '</soap:Body></soap:Envelope>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlRequest->asXML()), $xmlText);
	}

/**
 * testNamespace
 *
 * @retun void
 */
	public function testNamespace() {
		$xmlResponse = Xml::build('<root xmlns:ns="http://cakephp.org"><ns:tag id="1"><child>good</child><otherchild>bad</otherchild></ns:tag><tag>Tag without ns</tag></root>');
		$expected = array(
			'root' => array(
				'ns:tag' => array(
					'@id' => '1',
					'child' => 'good',
					'otherchild' => 'bad'
				),
				'tag' => 'Tag without ns'
			)
		);
		$this->assertEquals(Xml::toArray($xmlResponse), $expected);

		$xmlResponse = Xml::build('<root xmlns:ns="http://cakephp.org"><ns:tag id="1" /><tag><id>1</id></tag></root>');
		$expected = array(
			'root' => array(
				'ns:tag' => array(
					'@id' => '1'
				),
				'tag' => array(
					'id' => '1'
				)
			)
		);
		$this->assertEquals(Xml::toArray($xmlResponse), $expected);

		$xmlResponse = Xml::build('<root xmlns:ns="http://cakephp.org"><ns:attr>1</ns:attr></root>');
		$expected = array(
			'root' => array(
				'ns:attr' => '1'
			)
		);
		$this->assertEquals(Xml::toArray($xmlResponse), $expected);

		$xmlResponse = Xml::build('<root><ns:attr xmlns:ns="http://cakephp.org">1</ns:attr></root>');
		$this->assertEquals(Xml::toArray($xmlResponse), $expected);

		$xml = array(
			'root' => array(
				'ns:attr' => array(
					'xmlns:ns' => 'http://cakephp.org',
					'@' => 1
				)
			)
		);
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><root><ns:attr xmlns:ns="http://cakephp.org">1</ns:attr></root>';
		$xmlResponse = Xml::fromArray($xml);
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlResponse->asXML()), $expected);

		$xml = array(
			'root' => array(
				'tag' => array(
					'xmlns:pref' => 'http://cakephp.org',
					'pref:item' => array(
						'item 1',
						'item 2'
					)
				)
			)
		);
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><root><tag xmlns:pref="http://cakephp.org"><pref:item>item 1</pref:item><pref:item>item 2</pref:item></tag></root>';
		$xmlResponse = Xml::fromArray($xml);
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlResponse->asXML()), $expected);

		$xml = array(
			'root' => array(
				'tag' => array(
					'xmlns:' => 'http://cakephp.org'
				)
			)
		);
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><root><tag xmlns="http://cakephp.org"/></root>';
		$xmlResponse = Xml::fromArray($xml);
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlResponse->asXML()), $expected);

		$xml = array(
			'root' => array(
				'xmlns:' => 'http://cakephp.org'
			)
		);
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><root xmlns="http://cakephp.org"/>';
		$xmlResponse = Xml::fromArray($xml);
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlResponse->asXML()), $expected);

		$xml = array(
			'root' => array(
				'xmlns:ns' => 'http://cakephp.org'
			)
		);
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><root xmlns:ns="http://cakephp.org"/>';
		$xmlResponse = Xml::fromArray($xml);
		$this->assertEquals(str_replace(array("\r", "\n"), '', $xmlResponse->asXML()), $expected);
	}

/**
 * test that CDATA blocks don't get screwed up by SimpleXml
 *
 * @return void
 */
	public function testCdata() {
		$xml = '<' . '?xml version="1.0" encoding="UTF-8"?>' .
			'<people><name><![CDATA[ Mark ]]></name></people>';

		$result = Xml::build($xml);
		$this->assertEquals(' Mark ', (string)$result->name);
	}

/**
 * data provider for toArray() failures
 *
 * @return array
 */
	public static function invalidToArrayDataProvider() {
		return array(
			array(new DateTime()),
			array(array())
		);
	}

/**
 * testToArrayFail method
 *
 * @dataProvider invalidToArrayDataProvider
 * @expectedException XmlException
 */
	public function testToArrayFail($value) {
		Xml::toArray($value);
	}

/**
 * testWithModel method
 *
 * @return void
 */
	public function testWithModel() {
		$this->loadFixtures('User', 'Article');

		$user = new XmlUser();
		$data = $user->read(null, 1);

		$obj = Xml::build(compact('data'));
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><data>';
		$expected .= '<User><id>1</id><user>mariano</user><password>5f4dcc3b5aa765d61d8327deb882cf99</password>';
		$expected .= '<created>2007-03-17 01:16:23</created><updated>2007-03-17 01:18:31</updated></User>';
		$expected .= '<Article><id>1</id><user_id>1</user_id><title>First Article</title><body>First Article Body</body>';
		$expected .= '<published>Y</published><created>2007-03-18 10:39:23</created><updated>2007-03-18 10:41:31</updated></Article>';
		$expected .= '<Article><id>3</id><user_id>1</user_id><title>Third Article</title><body>Third Article Body</body>';
		$expected .= '<published>Y</published><created>2007-03-18 10:43:23</created><updated>2007-03-18 10:45:31</updated></Article>';
		$expected .= '</data>';
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $expected);
		
		//multiple model results - without a records key it would fatal error
		$data = $user->find('all', array('limit' => 2));
		$data = array('records' => $data);
		$obj = Xml::build(compact('data'));
		$expected = '<' . '?xml version="1.0" encoding="UTF-8"?><data>';
		$expected .= '<records>';
		$expected .= '<User><id>1</id><user>mariano</user><password>5f4dcc3b5aa765d61d8327deb882cf99</password>';
		$expected .= '<created>2007-03-17 01:16:23</created><updated>2007-03-17 01:18:31</updated></User>';
		$expected .= '<Article><id>1</id><user_id>1</user_id><title>First Article</title><body>First Article Body</body>';
		$expected .= '<published>Y</published><created>2007-03-18 10:39:23</created><updated>2007-03-18 10:41:31</updated></Article>';
		$expected .= '<Article><id>3</id><user_id>1</user_id><title>Third Article</title><body>Third Article Body</body>';
		$expected .= '<published>Y</published><created>2007-03-18 10:43:23</created><updated>2007-03-18 10:45:31</updated></Article>';
		$expected .= '</records><records><User><id>2</id><user>nate</user><password>5f4dcc3b5aa765d61d8327deb882cf99</password>';
		$expected .= '<created>2007-03-17 01:18:23</created><updated>2007-03-17 01:20:31</updated></User><Article/>';
		$expected .= '</records>';
		$expected .= '</data>';
		$result = $obj->asXML();
		$this->assertEquals(str_replace(array("\r", "\n"), '', $obj->asXML()), $expected);
	}

/**
 * Test ampersand in text elements.
 *
 * @return void
 */
	public function testAmpInText() {
		$data = array(
			'outer' => array(
				'inner' => array('name' => 'mark & mark')
			)
		);
		$obj = Xml::build($data);
		$result = $obj->asXml();
		$this->assertContains('mark &amp; mark', $result);
	}
}
