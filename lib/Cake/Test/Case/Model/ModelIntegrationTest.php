<?php
/**
 * ModelIntegrationTest file
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
 * @package       Cake.Test.Case.Model
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once dirname(__FILE__) . DS . 'ModelTestBase.php';
App::uses('DboSource', 'Model/Datasource');

/**
 * DboMock class
 * A Dbo Source driver to mock a connection and a identity name() method
 */
class DboMock extends DboSource {

/**
* Returns the $field without modifications
*/
	public function name($field) {
		return $field;
	}

/**
* Returns true to fake a database connection
*/
	public function connect() {
		return true;
	}
}

/**
 * ModelIntegrationTest
 *
 * @package       Cake.Test.Case.Model
 */
class ModelIntegrationTest extends BaseModelTest {

/**
 * testAssociationLazyLoading
 *
 * @group lazyloading
 * @return void
 */
	public function testAssociationLazyLoading() {
		$this->loadFixtures('ArticleFeaturedsTags');
		$Article = new ArticleFeatured();
		$this->assertTrue(isset($Article->belongsTo['User']));
		$this->assertFalse(property_exists($Article, 'User'));
		$this->assertInstanceOf('User', $Article->User);

		$this->assertTrue(isset($Article->belongsTo['Category']));
		$this->assertFalse(property_exists($Article, 'Category'));
		$this->assertTrue(isset($Article->Category));
		$this->assertInstanceOf('Category', $Article->Category);

		$this->assertTrue(isset($Article->hasMany['Comment']));
		$this->assertFalse(property_exists($Article, 'Comment'));
		$this->assertTrue(isset($Article->Comment));
		$this->assertInstanceOf('Comment', $Article->Comment);

		$this->assertTrue(isset($Article->hasAndBelongsToMany['Tag']));
		//There was not enough information to setup the association (joinTable and associationForeignKey)
		//so the model was not lazy loaded
		$this->assertTrue(property_exists($Article, 'Tag'));
		$this->assertTrue(isset($Article->Tag));
		$this->assertInstanceOf('Tag', $Article->Tag);

		$this->assertFalse(property_exists($Article, 'ArticleFeaturedsTag'));
		$this->assertInstanceOf('AppModel', $Article->ArticleFeaturedsTag);
		$this->assertEquals($Article->hasAndBelongsToMany['Tag']['joinTable'], 'article_featureds_tags');
		$this->assertEquals($Article->hasAndBelongsToMany['Tag']['associationForeignKey'], 'tag_id');
	}

/**
 * testAssociationLazyLoadWithHABTM
 *
 * @group lazyloading
 * @return void
 */
	public function testAssociationLazyLoadWithHABTM() {
		$this->loadFixtures('FruitsUuidTag', 'ArticlesTag');
		$this->db->cacheSources = false;
		$Article = new ArticleB();
		$this->assertTrue(isset($Article->hasAndBelongsToMany['TagB']));
		$this->assertFalse(property_exists($Article, 'TagB'));
		$this->assertInstanceOf('TagB', $Article->TagB);

		$this->assertFalse(property_exists($Article, 'ArticlesTag'));
		$this->assertInstanceOf('AppModel', $Article->ArticlesTag);

		$UuidTag = new UuidTag();
		$this->assertTrue(isset($UuidTag->hasAndBelongsToMany['Fruit']));
		$this->assertFalse(property_exists($UuidTag, 'Fruit'));
		$this->assertFalse(property_exists($UuidTag, 'FruitsUuidTag'));
		$this->assertTrue(isset($UuidTag->Fruit));

		$this->assertFalse(property_exists($UuidTag, 'FruitsUuidTag'));
		$this->assertTrue(isset($UuidTag->FruitsUuidTag));
		$this->assertInstanceOf('FruitsUuidTag', $UuidTag->FruitsUuidTag);
	}

/**
 * testAssociationLazyLoadWithBindModel
 *
 * @group lazyloading
 * @return void
 */
	public function testAssociationLazyLoadWithBindModel() {
		$this->loadFixtures('Article', 'User');
		$Article = new ArticleB();

		$this->assertFalse(isset($Article->belongsTo['User']));
		$this->assertFalse(property_exists($Article, 'User'));

		$Article->bindModel(array('belongsTo' => array('User')));
		$this->assertTrue(isset($Article->belongsTo['User']));
		$this->assertFalse(property_exists($Article, 'User'));
		$this->assertInstanceOf('User', $Article->User);
	}

/**
 * Tests that creating a model with no existent database table associated will throw an exception
 *
 * @expectedException MissingTableException
 * @return void
 */
	public function testMissingTable() {
		$Article = new ArticleB(false, uniqid());
		$Article->schema();
	}

/**
 * testPkInHAbtmLinkModelArticleB
 *
 * @return void
 */
	public function testPkInHabtmLinkModelArticleB() {
		$this->loadFixtures('Article', 'Tag', 'ArticlesTag');
		$TestModel2 = new ArticleB();
		$this->assertEquals($TestModel2->ArticlesTag->primaryKey, 'article_id');
	}

/**
 * Tests that $cacheSources can only be disabled in the db using model settings, not enabled
 *
 * @return void
 */
	public function testCacheSourcesDisabling() {
		$this->loadFixtures('JoinA', 'JoinB', 'JoinAB', 'JoinC', 'JoinAC');
		$this->db->cacheSources = true;
		$TestModel = new JoinA();
		$TestModel->cacheSources = false;
		$TestModel->setSource('join_as');
		$this->assertFalse($this->db->cacheSources);

		$this->db->cacheSources = false;
		$TestModel = new JoinA();
		$TestModel->cacheSources = true;
		$TestModel->setSource('join_as');
		$this->assertFalse($this->db->cacheSources);
	}

/**
 * testPkInHabtmLinkModel method
 *
	 * @return void
 */
	public function testPkInHabtmLinkModel() {
		//Test Nonconformant Models
		$this->loadFixtures('Content', 'ContentAccount', 'Account', 'JoinC', 'JoinAC', 'ItemsPortfolio');
		$TestModel = new Content();
		$this->assertEquals($TestModel->ContentAccount->primaryKey, 'iContentAccountsId');

		//test conformant models with no PK in the join table
		$this->loadFixtures('Article', 'Tag');
		$TestModel2 = new Article();
		$this->assertEquals($TestModel2->ArticlesTag->primaryKey, 'article_id');

		//test conformant models with PK in join table
		$TestModel3 = new Portfolio();
		$this->assertEquals($TestModel3->ItemsPortfolio->primaryKey, 'id');

		//test conformant models with PK in join table - join table contains extra field
		$this->loadFixtures('JoinA', 'JoinB', 'JoinAB');
		$TestModel4 = new JoinA();
		$this->assertEquals($TestModel4->JoinAsJoinB->primaryKey, 'id');

	}

/**
 * testDynamicBehaviorAttachment method
 *
 * @return void
 */
	public function testDynamicBehaviorAttachment() {
		$this->loadFixtures('Apple', 'Sample', 'Author');
		$TestModel = new Apple();
		$this->assertEquals(array(), $TestModel->Behaviors->attached());

		$TestModel->Behaviors->attach('Tree', array('left' => 'left_field', 'right' => 'right_field'));
		$this->assertTrue(is_object($TestModel->Behaviors->Tree));
		$this->assertEquals(array('Tree'), $TestModel->Behaviors->attached());

		$expected = array(
			'parent' => 'parent_id',
			'left' => 'left_field',
			'right' => 'right_field',
			'scope' => '1 = 1',
			'type' => 'nested',
			'__parentChange' => false,
			'recursive' => -1
		);
		$this->assertEquals($expected, $TestModel->Behaviors->Tree->settings['Apple']);

		$TestModel->Behaviors->attach('Tree', array('enabled' => false));
		$this->assertEquals($expected, $TestModel->Behaviors->Tree->settings['Apple']);
		$this->assertEquals(array('Tree'), $TestModel->Behaviors->attached());

		$TestModel->Behaviors->detach('Tree');
		$this->assertEquals(array(), $TestModel->Behaviors->attached());
		$this->assertFalse(isset($TestModel->Behaviors->Tree));
	}

/**
 * testFindWithJoinsOption method
 *
 * @access public
 * @return void
 */
	function testFindWithJoinsOption() {
		$this->loadFixtures('Article', 'User');
		$TestUser =& new User();

		$options = array(
			'fields' => array(
				'user',
				'Article.published',
			),
			'joins' => array(
				array(
					'table' => 'articles',
					'alias' => 'Article',
					'type' => 'LEFT',
					'conditions' => array(
						'User.id = Article.user_id',
					),
				),
			),
			'group' => array('User.user', 'Article.published'),
			'recursive' => -1,
			'order' => array('User.user')
		);
		$result = $TestUser->find('all', $options);
		$expected = array(
			array('User' => array('user' => 'garrett'), 'Article' => array('published' => '')),
			array('User' => array('user' => 'larry'), 'Article' => array('published' => 'Y')),
			array('User' => array('user' => 'mariano'), 'Article' => array('published' => 'Y')),
			array('User' => array('user' => 'nate'), 'Article' => array('published' => ''))
		);
		$this->assertEquals($expected, $result);
	}

/**
 * Tests cross database joins.  Requires $test and $test2 to both be set in DATABASE_CONFIG
 * NOTE: When testing on MySQL, you must set 'persistent' => false on *both* database connections,
 * or one connection will step on the other.
 */
	public function testCrossDatabaseJoins() {
		$config = new DATABASE_CONFIG();

		$skip = (!isset($config->test) || !isset($config->test2));
		if ($skip) {
			$this->markTestSkipped('Primary and secondary test databases not configured, skipping cross-database
				join tests.  To run theses tests defined $test and $test2 in your database configuration.'
			);
		}

		$this->loadFixtures('Article', 'Tag', 'ArticlesTag', 'User', 'Comment');
		$TestModel = new Article();

		$expected = array(
			array(
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => '1',
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array(
					array(
						'id' => '1',
						'article_id' => '1',
						'user_id' => '2',
						'comment' => 'First Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:45:23',
						'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => '2',
						'article_id' => '1',
						'user_id' => '4',
						'comment' => 'Second Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:47:23',
						'updated' => '2007-03-18 10:49:31'
					),
					array(
						'id' => '3',
						'article_id' => '1',
						'user_id' => '1',
						'comment' => 'Third Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:49:23',
						'updated' => '2007-03-18 10:51:31'
					),
					array(
						'id' => '4',
						'article_id' => '1',
						'user_id' => '1',
						'comment' => 'Fourth Comment for First Article',
						'published' => 'N',
						'created' => '2007-03-18 10:51:23',
						'updated' => '2007-03-18 10:53:31'
				)),
				'Tag' => array(
					array(
						'id' => '1',
						'tag' => 'tag1',
						'created' => '2007-03-18 12:22:23',
						'updated' => '2007-03-18 12:24:31'
					),
					array(
						'id' => '2',
						'tag' => 'tag2',
						'created' => '2007-03-18 12:24:23',
						'updated' => '2007-03-18 12:26:31'
			))),
			array(
				'Article' => array(
					'id' => '2',
					'user_id' => '3',
					'title' => 'Second Article',
					'body' => 'Second Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => '3',
					'user' => 'larry',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23',
					'updated' => '2007-03-17 01:22:31'
				),
				'Comment' => array(
					array(
						'id' => '5',
						'article_id' => '2',
						'user_id' => '1',
						'comment' => 'First Comment for Second Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:53:23',
						'updated' => '2007-03-18 10:55:31'
					),
					array(
						'id' => '6',
						'article_id' => '2',
						'user_id' => '2',
						'comment' => 'Second Comment for Second Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:55:23',
						'updated' => '2007-03-18 10:57:31'
				)),
				'Tag' => array(
					array(
						'id' => '1',
						'tag' => 'tag1',
						'created' => '2007-03-18 12:22:23',
						'updated' => '2007-03-18 12:24:31'
					),
					array(
						'id' => '3',
						'tag' => 'tag3',
						'created' => '2007-03-18 12:26:23',
						'updated' => '2007-03-18 12:28:31'
			))),
			array(
				'Article' => array(
					'id' => '3',
					'user_id' => '1',
					'title' => 'Third Article',
					'body' => 'Third Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:43:23',
					'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => '1',
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array(),
				'Tag' => array()
		));
		$this->assertEquals($TestModel->find('all'), $expected);

		$db2 = ConnectionManager::getDataSource('test2');
		$this->fixtureManager->loadSingle('User', $db2);
		$this->fixtureManager->loadSingle('Comment', $db2);
		$this->assertEquals($TestModel->find('count'), 3);

		$TestModel->User->setDataSource('test2');
		$TestModel->Comment->setDataSource('test2');

		foreach ($expected as $key => $value) {
			unset($value['Comment'], $value['Tag']);
			$expected[$key] = $value;
		}

		$TestModel->recursive = 0;
		$result = $TestModel->find('all');
		$this->assertEquals($expected, $result);

		foreach ($expected as $key => $value) {
			unset($value['Comment'], $value['Tag']);
			$expected[$key] = $value;
		}

		$TestModel->recursive = 0;
		$result = $TestModel->find('all');
		$this->assertEquals($expected, $result);

		$result = Set::extract($TestModel->User->find('all'), '{n}.User.id');
		$this->assertEquals($result, array('1', '2', '3', '4'));
		$this->assertEquals($TestModel->find('all'), $expected);

		$TestModel->Comment->unbindModel(array('hasOne' => array('Attachment')));
		$expected = array(
			array(
				'Comment' => array(
					'id' => '1',
					'article_id' => '1',
					'user_id' => '2',
					'comment' => 'First Comment for First Article',
					'published' => 'Y',
					'created' => '2007-03-18 10:45:23',
					'updated' => '2007-03-18 10:47:31'
				),
				'User' => array(
					'id' => '2',
					'user' => 'nate',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23',
					'updated' => '2007-03-17 01:20:31'
				),
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
			)),
			array(
				'Comment' => array(
					'id' => '2',
					'article_id' => '1',
					'user_id' => '4',
					'comment' => 'Second Comment for First Article',
					'published' => 'Y',
					'created' => '2007-03-18 10:47:23',
					'updated' => '2007-03-18 10:49:31'
				),
				'User' => array(
					'id' => '4',
					'user' => 'garrett',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23',
					'updated' => '2007-03-17 01:24:31'
				),
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
			)),
			array(
				'Comment' => array(
					'id' => '3',
					'article_id' => '1',
					'user_id' => '1',
					'comment' => 'Third Comment for First Article',
					'published' => 'Y',
					'created' => '2007-03-18 10:49:23',
					'updated' => '2007-03-18 10:51:31'
				),
				'User' => array(
					'id' => '1',
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
			)),
			array(
				'Comment' => array(
					'id' => '4',
					'article_id' => '1',
					'user_id' => '1',
					'comment' => 'Fourth Comment for First Article',
					'published' => 'N',
					'created' => '2007-03-18 10:51:23',
					'updated' => '2007-03-18 10:53:31'
				),
				'User' => array(
					'id' => '1',
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
			)),
			array(
				'Comment' => array(
					'id' => '5',
					'article_id' => '2',
					'user_id' => '1',
					'comment' => 'First Comment for Second Article',
					'published' => 'Y',
					'created' => '2007-03-18 10:53:23',
					'updated' => '2007-03-18 10:55:31'
				),
				'User' => array(
					'id' => '1',
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'Article' => array(
					'id' => '2',
					'user_id' => '3',
					'title' => 'Second Article',
					'body' => 'Second Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
			)),
			array(
				'Comment' => array(
					'id' => '6',
					'article_id' => '2',
					'user_id' => '2',
					'comment' => 'Second Comment for Second Article',
					'published' => 'Y',
					'created' => '2007-03-18 10:55:23',
					'updated' => '2007-03-18 10:57:31'
				),
				'User' => array(
					'id' => '2',
					'user' => 'nate',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23',
					'updated' => '2007-03-17 01:20:31'
				),
				'Article' => array(
					'id' => '2',
					'user_id' => '3',
					'title' => 'Second Article',
					'body' => 'Second Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
		)));
		$this->assertEquals($TestModel->Comment->find('all'), $expected);
	}

/**
 * testDisplayField method
 *
 * @return void
 */
	public function testDisplayField() {
		$this->loadFixtures('Post', 'Comment', 'Person', 'User');
		$Post = new Post();
		$Comment = new Comment();
		$Person = new Person();

		$this->assertEquals($Post->displayField, 'title');
		$this->assertEquals($Person->displayField, 'name');
		$this->assertEquals($Comment->displayField, 'id');
	}

/**
 * testSchema method
 *
 * @return void
 */
	public function testSchema() {
		$Post = new Post();

		$result = $Post->schema();
		$columns = array('id', 'author_id', 'title', 'body', 'published', 'created', 'updated');
		$this->assertEquals(array_keys($result), $columns);

		$types = array('integer', 'integer', 'string', 'text', 'string', 'datetime', 'datetime');
		$this->assertEquals(Set::extract(array_values($result), '{n}.type'), $types);

		$result = $Post->schema('body');
		$this->assertEquals($result['type'], 'text');
		$this->assertNull($Post->schema('foo'));

		$this->assertEquals($Post->getColumnTypes(), array_combine($columns, $types));
	}

/**
 * data provider for time tests.
 *
 * @return array
 */
	public static function timeProvider() {
		$db = ConnectionManager::getDataSource('test');
		$now = $db->expression('NOW()');
		return array(
			// blank
			array(
				array('hour' => '', 'min' => '', 'meridian' => ''),
				''
			),
			// missing hour
			array(
				array('hour' => '', 'min' => '00', 'meridian' => 'pm'),
				''
			),
			// all blank
			array(
				array('hour' => '', 'min' => '', 'sec' => ''),
				''
			),
			// set and empty merdian 
			array(
				array('hour' => '1', 'min' => '00', 'meridian' => ''),
				''
			),
			// midnight
			array(
				array('hour' => '12', 'min' => '0', 'meridian' => 'am'),
				'00:00:00'
			),
			array(
				array('hour' => '00', 'min' => '00'),
				'00:00:00'
			),
			// 3am
			array(
				array('hour' => '03', 'min' => '04', 'sec' => '04'),
				'03:04:04'
			),
			array(
				array('hour' => '3', 'min' => '4', 'sec' => '4'),
				'03:04:04'
			),
			array(
				array('hour' => '03', 'min' => '4', 'sec' => '4'),
				'03:04:04'
			),
			array(
				$now,
				$now
			)
		);
	}

/**
 * test deconstruct with time fields.
 *
 * @dataProvider timeProvider
 * @return void
 */
	public function testDeconstructFieldsTime($input, $result) {
		$this->skipIf($this->db instanceof Sqlserver, 'This test is not compatible with SQL Server.');

		$this->loadFixtures('Apple');
		$TestModel = new Apple();

		$data = array(
			'Apple' => array(
				'mytime' => $input
			)
		);

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('mytime' => $result));
		$this->assertEquals($TestModel->data, $expected);
	}

/**
 * testDeconstructFields with datetime, timestamp, and date fields
 *
 * @return void
 */
	public function testDeconstructFieldsDateTime() {
		$this->skipIf($this->db instanceof Sqlserver, 'This test is not compatible with SQL Server.');

		$this->loadFixtures('Apple');
		$TestModel = new Apple();

		//test null/empty values first
		$data['Apple']['created']['year'] = '';
		$data['Apple']['created']['month'] = '';
		$data['Apple']['created']['day'] = '';
		$data['Apple']['created']['hour'] = '';
		$data['Apple']['created']['min'] = '';
		$data['Apple']['created']['sec'] = '';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['date']['year'] = '';
		$data['Apple']['date']['month'] = '';
		$data['Apple']['date']['day'] = '';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('date' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '2007';
		$data['Apple']['created']['month'] = '08';
		$data['Apple']['created']['day'] = '20';
		$data['Apple']['created']['hour'] = '';
		$data['Apple']['created']['min'] = '';
		$data['Apple']['created']['sec'] = '';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => '2007-08-20 00:00:00'));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '2007';
		$data['Apple']['created']['month'] = '08';
		$data['Apple']['created']['day'] = '20';
		$data['Apple']['created']['hour'] = '10';
		$data['Apple']['created']['min'] = '12';
		$data['Apple']['created']['sec'] = '';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => '2007-08-20 10:12:00'));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '2007';
		$data['Apple']['created']['month'] = '';
		$data['Apple']['created']['day'] = '12';
		$data['Apple']['created']['hour'] = '20';
		$data['Apple']['created']['min'] = '';
		$data['Apple']['created']['sec'] = '';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['hour'] = '20';
		$data['Apple']['created']['min'] = '33';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['hour'] = '20';
		$data['Apple']['created']['min'] = '33';
		$data['Apple']['created']['sec'] = '33';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['hour'] = '13';
		$data['Apple']['created']['min'] = '00';
		$data['Apple']['date']['year'] = '2006';
		$data['Apple']['date']['month'] = '12';
		$data['Apple']['date']['day'] = '25';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array(
			'Apple' => array(
			'created' => '',
			'date' => '2006-12-25'
		));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '2007';
		$data['Apple']['created']['month'] = '08';
		$data['Apple']['created']['day'] = '20';
		$data['Apple']['created']['hour'] = '10';
		$data['Apple']['created']['min'] = '12';
		$data['Apple']['created']['sec'] = '09';
		$data['Apple']['date']['year'] = '2006';
		$data['Apple']['date']['month'] = '12';
		$data['Apple']['date']['day'] = '25';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array(
			'Apple' => array(
				'created' => '2007-08-20 10:12:09',
				'date' => '2006-12-25'
		));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '--';
		$data['Apple']['created']['month'] = '--';
		$data['Apple']['created']['day'] = '--';
		$data['Apple']['created']['hour'] = '--';
		$data['Apple']['created']['min'] = '--';
		$data['Apple']['created']['sec'] = '--';
		$data['Apple']['date']['year'] = '--';
		$data['Apple']['date']['month'] = '--';
		$data['Apple']['date']['day'] = '--';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => '', 'date' => ''));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['created']['year'] = '2007';
		$data['Apple']['created']['month'] = '--';
		$data['Apple']['created']['day'] = '20';
		$data['Apple']['created']['hour'] = '10';
		$data['Apple']['created']['min'] = '12';
		$data['Apple']['created']['sec'] = '09';
		$data['Apple']['date']['year'] = '2006';
		$data['Apple']['date']['month'] = '12';
		$data['Apple']['date']['day'] = '25';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('created' => '', 'date' => '2006-12-25'));
		$this->assertEquals($TestModel->data, $expected);

		$data = array();
		$data['Apple']['date']['year'] = '2006';
		$data['Apple']['date']['month'] = '12';
		$data['Apple']['date']['day'] = '25';

		$TestModel->data = null;
		$TestModel->set($data);
		$expected = array('Apple' => array('date' => '2006-12-25'));
		$this->assertEquals($TestModel->data, $expected);

		$db = ConnectionManager::getDataSource('test');
		$data = array();
		$data['Apple']['modified'] = $db->expression('NOW()');
		$TestModel->data = null;
		$TestModel->set($data);
		$this->assertEquals($TestModel->data, $data);
	}

/**
 * testTablePrefixSwitching method
 *
 * @return void
 */
	public function testTablePrefixSwitching() {
		ConnectionManager::create('database1',
				array_merge($this->db->config, array('prefix' => 'aaa_')
		));
		ConnectionManager::create('database2',
			array_merge($this->db->config, array('prefix' => 'bbb_')
		));

		$db1 = ConnectionManager::getDataSource('database1');
		$db2 = ConnectionManager::getDataSource('database2');

		$TestModel = new Apple();
		$TestModel->setDataSource('database1');
		$this->assertEquals($this->db->fullTableName($TestModel, false), 'aaa_apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'aaa_apples');
		$this->assertEquals($db2->fullTableName($TestModel, false), 'aaa_apples');

		$TestModel->setDataSource('database2');
		$this->assertEquals($this->db->fullTableName($TestModel, false), 'bbb_apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'bbb_apples');
		$this->assertEquals($db2->fullTableName($TestModel, false), 'bbb_apples');

		$TestModel = new Apple();
		$TestModel->tablePrefix = 'custom_';
		$this->assertEquals($this->db->fullTableName($TestModel, false), 'custom_apples');
		$TestModel->setDataSource('database1');
		$this->assertEquals($this->db->fullTableName($TestModel, false), 'custom_apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'custom_apples');

		$TestModel = new Apple();
		$TestModel->setDataSource('database1');
		$this->assertEquals($this->db->fullTableName($TestModel, false), 'aaa_apples');
		$TestModel->tablePrefix = '';
		$TestModel->setDataSource('database2');
		$this->assertEquals($db2->fullTableName($TestModel, false), 'apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'apples');

		$TestModel->tablePrefix = null;
		$TestModel->setDataSource('database1');
		$this->assertEquals($db2->fullTableName($TestModel, false), 'aaa_apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'aaa_apples');

		$TestModel->tablePrefix = false;
		$TestModel->setDataSource('database2');
		$this->assertEquals($db2->fullTableName($TestModel, false), 'apples');
		$this->assertEquals($db1->fullTableName($TestModel, false), 'apples');
	}

/**
 * Tests validation parameter order in custom validation methods
 *
 * @return void
 */
	public function testInvalidAssociation() {
		$TestModel = new ValidationTest1();
		$this->assertNull($TestModel->getAssociated('Foo'));
	}

/**
 * testLoadModelSecondIteration method
 *
 * @return void
 */
	public function testLoadModelSecondIteration() {
		$this->loadFixtures('Apple', 'Message', 'Thread', 'Bid');
		$model = new ModelA();
		$this->assertInstanceOf('ModelA', $model);

		$this->assertInstanceOf('ModelB', $model->ModelB);
		$this->assertInstanceOf('ModelD', $model->ModelB->ModelD);

		$this->assertInstanceOf('ModelC', $model->ModelC);
		$this->assertInstanceOf('ModelD', $model->ModelC->ModelD);
	}

/**
 * ensure that exists() does not persist between method calls reset on create
 *
 * @return void
 */
	public function testResetOfExistsOnCreate() {
		$this->loadFixtures('Article');
		$Article = new Article();
		$Article->id = 1;
		$Article->saveField('title', 'Reset me');
		$Article->delete();
		$Article->id = 1;
		$this->assertFalse($Article->exists());

		$Article->create();
		$this->assertFalse($Article->exists());
		$Article->id = 2;
		$Article->saveField('title', 'Staying alive');
		$result = $Article->read(null, 2);
		$this->assertEquals($result['Article']['title'], 'Staying alive');
	}

/**
 * testUseTableFalseExistsCheck method
 *
 * @return void
 */
	public function testUseTableFalseExistsCheck() {
		$this->loadFixtures('Article');
		$Article = new Article();
		$Article->id = 1337;
		$result = $Article->exists();
		$this->assertFalse($result);

		$Article->useTable = false;
		$Article->id = null;
		$result = $Article->exists();
		$this->assertFalse($result);

		// An article with primary key of '1' has been loaded by the fixtures.
		$Article->useTable = false;
		$Article->id = 1;
		$result = $Article->exists();
		$this->assertTrue($result);
	}

/**
 * testPluginAssociations method
 *
 * @return void
 */
	public function testPluginAssociations() {
		$this->loadFixtures('TestPluginArticle', 'User', 'TestPluginComment');
		$TestModel = new TestPluginArticle();

		$result = $TestModel->find('all');
		$expected = array(
			array(
				'TestPluginArticle' => array(
					'id' => 1,
					'user_id' => 1,
					'title' => 'First Plugin Article',
					'body' => 'First Plugin Article Body',
					'published' => 'Y',
					'created' => '2008-09-24 10:39:23',
					'updated' => '2008-09-24 10:41:31'
				),
				'User' => array(
					'id' => 1,
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'TestPluginComment' => array(
					array(
						'id' => 1,
						'article_id' => 1,
						'user_id' => 2,
						'comment' => 'First Comment for First Plugin Article',
						'published' => 'Y',
						'created' => '2008-09-24 10:45:23',
						'updated' => '2008-09-24 10:47:31'
					),
					array(
						'id' => 2,
						'article_id' => 1,
						'user_id' => 4,
						'comment' => 'Second Comment for First Plugin Article',
						'published' => 'Y',
						'created' => '2008-09-24 10:47:23',
						'updated' => '2008-09-24 10:49:31'
					),
					array(
						'id' => 3,
						'article_id' => 1,
						'user_id' => 1,
						'comment' => 'Third Comment for First Plugin Article',
						'published' => 'Y',
						'created' => '2008-09-24 10:49:23',
						'updated' => '2008-09-24 10:51:31'
					),
					array(
						'id' => 4,
						'article_id' => 1,
						'user_id' => 1,
						'comment' => 'Fourth Comment for First Plugin Article',
						'published' => 'N',
						'created' => '2008-09-24 10:51:23',
						'updated' => '2008-09-24 10:53:31'
			))),
			array(
				'TestPluginArticle' => array(
					'id' => 2,
					'user_id' => 3,
					'title' => 'Second Plugin Article',
					'body' => 'Second Plugin Article Body',
					'published' => 'Y',
					'created' => '2008-09-24 10:41:23',
					'updated' => '2008-09-24 10:43:31'
				),
				'User' => array(
					'id' => 3,
					'user' => 'larry',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23',
					'updated' => '2007-03-17 01:22:31'
				),
				'TestPluginComment' => array(
					array(
						'id' => 5,
						'article_id' => 2,
						'user_id' => 1,
						'comment' => 'First Comment for Second Plugin Article',
						'published' => 'Y',
						'created' => '2008-09-24 10:53:23',
						'updated' => '2008-09-24 10:55:31'
					),
					array(
						'id' => 6,
						'article_id' => 2,
						'user_id' => 2,
						'comment' => 'Second Comment for Second Plugin Article',
						'published' => 'Y',
						'created' => '2008-09-24 10:55:23',
						'updated' => '2008-09-24 10:57:31'
			))),
			array(
				'TestPluginArticle' => array(
					'id' => 3,
					'user_id' => 1,
					'title' => 'Third Plugin Article',
					'body' => 'Third Plugin Article Body',
					'published' => 'Y',
					'created' => '2008-09-24 10:43:23',
					'updated' => '2008-09-24 10:45:31'
				),
				'User' => array(
					'id' => 1,
					'user' => 'mariano',
					'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23',
					'updated' => '2007-03-17 01:18:31'
				),
				'TestPluginComment' => array()
		));

		$this->assertEquals($expected, $result);
	}

/**
 * Tests getAssociated method
 *
 * @return void
 */
	public function testGetAssociated() {
		$this->loadFixtures('Article', 'Tag');
		$Article = ClassRegistry::init('Article');

		$assocTypes = array('hasMany', 'hasOne', 'belongsTo', 'hasAndBelongsToMany');
		foreach ($assocTypes as $type) {
			 $this->assertEquals($Article->getAssociated($type), array_keys($Article->{$type}));
		}

		$Article->bindModel(array('hasMany' => array('Category')));
		$this->assertEquals($Article->getAssociated('hasMany'), array('Comment', 'Category'));

		$results = $Article->getAssociated();
		$results = array_keys($results);
		sort($results);
		$this->assertEquals($results, array('Category', 'Comment', 'Tag', 'User'));

		$Article->unbindModel(array('hasAndBelongsToMany' => array('Tag')));
		$this->assertEquals($Article->getAssociated('hasAndBelongsToMany'), array());

		$result = $Article->getAssociated('Category');
		$expected = array(
			'className' => 'Category',
			'foreignKey' => 'article_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'dependent' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
			'association' => 'hasMany',
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testAutoConstructAssociations method
 *
 * @return void
 */
	public function testAutoConstructAssociations() {
		$this->loadFixtures('User', 'ArticleFeatured', 'Featured', 'ArticleFeaturedsTags');
		$TestModel = new AssociationTest1();

		$result = $TestModel->hasAndBelongsToMany;
		$expected = array('AssociationTest2' => array(
				'unique' => false,
				'joinTable' => 'join_as_join_bs',
				'foreignKey' => false,
				'className' => 'AssociationTest2',
				'with' => 'JoinAsJoinB',
				'dynamicWith' => true,
				'associationForeignKey' => 'join_b_id',
				'conditions' => '', 'fields' => '', 'order' => '', 'limit' => '', 'offset' => '',
				'finderQuery' => '', 'deleteQuery' => '', 'insertQuery' => ''
		));
		$this->assertEquals($expected, $result);

		$TestModel = new ArticleFeatured();
		$TestFakeModel = new ArticleFeatured(array('table' => false));

		$expected = array(
			'User' => array(
				'className' => 'User', 'foreignKey' => 'user_id',
				'conditions' => '', 'fields' => '', 'order' => '', 'counterCache' => ''
			),
			'Category' => array(
				'className' => 'Category', 'foreignKey' => 'category_id',
				'conditions' => '', 'fields' => '', 'order' => '', 'counterCache' => ''
			)
		);
		$this->assertSame($TestModel->belongsTo, $expected);
		$this->assertSame($TestFakeModel->belongsTo, $expected);

		$this->assertEquals($TestModel->User->name, 'User');
		$this->assertEquals($TestFakeModel->User->name, 'User');
		$this->assertEquals($TestModel->Category->name, 'Category');
		$this->assertEquals($TestFakeModel->Category->name, 'Category');

		$expected = array(
			'Featured' => array(
				'className' => 'Featured',
				'foreignKey' => 'article_featured_id',
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'dependent' => ''
		));

		$this->assertSame($TestModel->hasOne, $expected);
		$this->assertSame($TestFakeModel->hasOne, $expected);

		$this->assertEquals($TestModel->Featured->name, 'Featured');
		$this->assertEquals($TestFakeModel->Featured->name, 'Featured');

		$expected = array(
			'Comment' => array(
				'className' => 'Comment',
				'dependent' => true,
				'foreignKey' => 'article_featured_id',
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		));

		$this->assertSame($TestModel->hasMany, $expected);
		$this->assertSame($TestFakeModel->hasMany, $expected);

		$this->assertEquals($TestModel->Comment->name, 'Comment');
		$this->assertEquals($TestFakeModel->Comment->name, 'Comment');

		$expected = array(
			'Tag' => array(
				'className' => 'Tag',
				'joinTable' => 'article_featureds_tags',
				'with' => 'ArticleFeaturedsTag',
				'dynamicWith' => true,
				'foreignKey' => 'article_featured_id',
				'associationForeignKey' => 'tag_id',
				'conditions' => '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'unique' => true,
				'finderQuery' => '',
				'deleteQuery' => '',
				'insertQuery' => ''
		));

		$this->assertSame($TestModel->hasAndBelongsToMany, $expected);
		$this->assertSame($TestFakeModel->hasAndBelongsToMany, $expected);

		$this->assertEquals($TestModel->Tag->name, 'Tag');
		$this->assertEquals($TestFakeModel->Tag->name, 'Tag');
	}

/**
 * test creating associations with plugins. Ensure a double alias isn't created
 *
 * @return void
 */
	public function testAutoConstructPluginAssociations() {
		$Comment = ClassRegistry::init('TestPluginComment');

		$this->assertEquals(2, count($Comment->belongsTo), 'Too many associations');
		$this->assertFalse(isset($Comment->belongsTo['TestPlugin.User']));
		$this->assertTrue(isset($Comment->belongsTo['User']), 'Missing association');
		$this->assertTrue(isset($Comment->belongsTo['TestPluginArticle']), 'Missing association');
	}

/**
 * test Model::__construct
 *
 * ensure that $actsAS and $findMethods are merged.
 *
 * @return void
 */
	public function testConstruct() {
		$this->loadFixtures('Post');

		$TestModel = ClassRegistry::init('MergeVarPluginPost');
		$this->assertEquals($TestModel->actsAs, array('Containable' => null, 'Tree' => null));
		$this->assertTrue(isset($TestModel->Behaviors->Containable));
		$this->assertTrue(isset($TestModel->Behaviors->Tree));

		$TestModel = ClassRegistry::init('MergeVarPluginComment');
		$expected = array('Containable' => array('some_settings'));
		$this->assertEquals($TestModel->actsAs, $expected);
		$this->assertTrue(isset($TestModel->Behaviors->Containable));
	}

/**
 * test Model::__construct
 *
 * ensure that $actsAS and $findMethods are merged.
 *
 * @return void
 */
	public function testConstructWithAlternateDataSource() {
		$TestModel = ClassRegistry::init(array(
			'class' => 'DoesntMatter', 'ds' => 'test', 'table' => false
		));
		$this->assertEquals('test', $TestModel->useDbConfig);

		//deprecated but test it anyway
		$NewVoid = new TheVoid(null, false, 'other');
		$this->assertEquals('other', $NewVoid->useDbConfig);
	}

/**
 * testColumnTypeFetching method
 *
 * @return void
 */
	public function testColumnTypeFetching() {
		$model = new Test();
		$this->assertEquals($model->getColumnType('id'), 'integer');
		$this->assertEquals($model->getColumnType('notes'), 'text');
		$this->assertEquals($model->getColumnType('updated'), 'datetime');
		$this->assertEquals($model->getColumnType('unknown'), null);

		$model = new Article();
		$this->assertEquals($model->getColumnType('User.created'), 'datetime');
		$this->assertEquals($model->getColumnType('Tag.id'), 'integer');
		$this->assertEquals($model->getColumnType('Article.id'), 'integer');
	}

/**
 * testHabtmUniqueKey method
 *
 * @return void
 */
	public function testHabtmUniqueKey() {
		$model = new Item();
		$this->assertFalse($model->hasAndBelongsToMany['Portfolio']['unique']);
	}

/**
 * testIdentity method
 *
 * @return void
 */
	public function testIdentity() {
		$TestModel = new Test();
		$result = $TestModel->alias;
		$expected = 'Test';
		$this->assertEquals($expected, $result);

		$TestModel = new TestAlias();
		$result = $TestModel->alias;
		$expected = 'TestAlias';
		$this->assertEquals($expected, $result);

		$TestModel = new Test(array('alias' => 'AnotherTest'));
		$result = $TestModel->alias;
		$expected = 'AnotherTest';
		$this->assertEquals($expected, $result);
	}

/**
 * testWithAssociation method
 *
 * @return void
 */
	public function testWithAssociation() {
		$this->loadFixtures('Something', 'SomethingElse', 'JoinThing');
		$TestModel = new Something();
		$result = $TestModel->SomethingElse->find('all');

		$expected = array(
			array(
				'SomethingElse' => array(
					'id' => '1',
					'title' => 'First Post',
					'body' => 'First Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
				),
				'Something' => array(
					array(
						'id' => '3',
						'title' => 'Third Post',
						'body' => 'Third Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:43:23',
						'updated' => '2007-03-18 10:45:31',
						'JoinThing' => array(
							'id' => '3',
							'something_id' => '3',
							'something_else_id' => '1',
							'doomed' => true,
							'created' => '2007-03-18 10:43:23',
							'updated' => '2007-03-18 10:45:31'
			)))),
			array(
				'SomethingElse' => array(
					'id' => '2',
					'title' => 'Second Post',
					'body' => 'Second Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
				),
				'Something' => array(
					array(
						'id' => '1',
						'title' => 'First Post',
						'body' => 'First Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:39:23',
						'updated' => '2007-03-18 10:41:31',
						'JoinThing' => array(
							'id' => '1',
							'something_id' => '1',
							'something_else_id' => '2',
							'doomed' => true,
							'created' => '2007-03-18 10:39:23',
							'updated' => '2007-03-18 10:41:31'
			)))),
			array(
				'SomethingElse' => array(
					'id' => '3',
					'title' => 'Third Post',
					'body' => 'Third Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:43:23',
					'updated' => '2007-03-18 10:45:31'
				),
				'Something' => array(
					array(
						'id' => '2',
						'title' => 'Second Post',
						'body' => 'Second Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:41:23',
						'updated' => '2007-03-18 10:43:31',
						'JoinThing' => array(
							'id' => '2',
							'something_id' => '2',
							'something_else_id' => '3',
							'doomed' => false,
							'created' => '2007-03-18 10:41:23',
							'updated' => '2007-03-18 10:43:31'
		)))));
		$this->assertEquals($expected, $result);

		$result = $TestModel->find('all');
		$expected = array(
			array(
				'Something' => array(
					'id' => '1',
					'title' => 'First Post',
					'body' => 'First Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
				),
				'SomethingElse' => array(
					array(
						'id' => '2',
						'title' => 'Second Post',
						'body' => 'Second Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:41:23',
						'updated' => '2007-03-18 10:43:31',
						'JoinThing' => array(
							'doomed' => true,
							'something_id' => '1',
							'something_else_id' => '2'
			)))),
			array(
				'Something' => array(
					'id' => '2',
					'title' => 'Second Post',
					'body' => 'Second Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
				),
				'SomethingElse' => array(
					array(
						'id' => '3',
						'title' => 'Third Post',
						'body' => 'Third Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:43:23',
						'updated' => '2007-03-18 10:45:31',
						'JoinThing' => array(
							'doomed' => false,
							'something_id' => '2',
							'something_else_id' => '3'
			)))),
			array(
				'Something' => array(
					'id' => '3',
					'title' => 'Third Post',
					'body' => 'Third Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:43:23',
					'updated' => '2007-03-18 10:45:31'
				),
				'SomethingElse' => array(
					array(
						'id' => '1',
						'title' => 'First Post',
						'body' => 'First Post Body',
						'published' => 'Y',
						'created' => '2007-03-18 10:39:23',
						'updated' => '2007-03-18 10:41:31',
						'JoinThing' => array(
							'doomed' => true,
							'something_id' => '3',
							'something_else_id' => '1'
		)))));
		$this->assertEquals($expected, $result);

		$result = $TestModel->findById(1);
		$expected = array(
			'Something' => array(
				'id' => '1',
				'title' => 'First Post',
				'body' => 'First Post Body',
				'published' => 'Y',
				'created' => '2007-03-18 10:39:23',
				'updated' => '2007-03-18 10:41:31'
			),
			'SomethingElse' => array(
				array(
					'id' => '2',
					'title' => 'Second Post',
					'body' => 'Second Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31',
					'JoinThing' => array(
						'doomed' => true,
						'something_id' => '1',
						'something_else_id' => '2'
		))));
		$this->assertEquals($expected, $result);

		$expected = $TestModel->findById(1);
		$TestModel->set($expected);
		$TestModel->save();
		$result = $TestModel->findById(1);
		$this->assertEquals($expected, $result);

		$TestModel->hasAndBelongsToMany['SomethingElse']['unique'] = false;
		$TestModel->create(array(
			'Something' => array('id' => 1),
			'SomethingElse' => array(3, array(
				'something_else_id' => 1,
				'doomed' => true
		))));

		$ts = date('Y-m-d H:i:s');
		$TestModel->save();

		$TestModel->hasAndBelongsToMany['SomethingElse']['order'] = 'SomethingElse.id ASC';
		$result = $TestModel->findById(1);
		$expected = array(
			'Something' => array(
				'id' => '1',
				'title' => 'First Post',
				'body' => 'First Post Body',
				'published' => 'Y',
				'created' => '2007-03-18 10:39:23'
			),
			'SomethingElse' => array(
				array(
					'id' => '1',
					'title' => 'First Post',
					'body' => 'First Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31',
					'JoinThing' => array(
						'doomed' => true,
						'something_id' => '1',
						'something_else_id' => '1'
				)
			),
				array(
					'id' => '2',
					'title' => 'Second Post',
					'body' => 'Second Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31',
					'JoinThing' => array(
						'doomed' => true,
						'something_id' => '1',
						'something_else_id' => '2'
				)
			),
				array(
					'id' => '3',
					'title' => 'Third Post',
					'body' => 'Third Post Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:43:23',
					'updated' => '2007-03-18 10:45:31',
					'JoinThing' => array(
						'doomed' => false,
						'something_id' => '1',
						'something_else_id' => '3')
					)
				)
			);
		$this->assertTrue($result['Something']['updated'] >= $ts);
		unset($result['Something']['updated']);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindSelfAssociations method
 *
 * @return void
 */
	public function testFindSelfAssociations() {
		$this->loadFixtures('Person');

		$TestModel = new Person();
		$TestModel->recursive = 2;
		$result = $TestModel->read(null, 1);
		$expected = array(
			'Person' => array(
				'id' => 1,
				'name' => 'person',
				'mother_id' => 2,
				'father_id' => 3
			),
			'Mother' => array(
				'id' => 2,
				'name' => 'mother',
				'mother_id' => 4,
				'father_id' => 5,
				'Mother' => array(
					'id' => 4,
					'name' => 'mother - grand mother',
					'mother_id' => 0,
					'father_id' => 0
				),
				'Father' => array(
					'id' => 5,
					'name' => 'mother - grand father',
					'mother_id' => 0,
					'father_id' => 0
			)),
			'Father' => array(
				'id' => 3,
				'name' => 'father',
				'mother_id' => 6,
				'father_id' => 7,
				'Father' => array(
					'id' => 7,
					'name' => 'father - grand father',
					'mother_id' => 0,
					'father_id' => 0
				),
				'Mother' => array(
					'id' => 6,
					'name' => 'father - grand mother',
					'mother_id' => 0,
					'father_id' => 0
		)));

		$this->assertEquals($expected, $result);

		$TestModel->recursive = 3;
		$result = $TestModel->read(null, 1);
		$expected = array(
			'Person' => array(
				'id' => 1,
				'name' => 'person',
				'mother_id' => 2,
				'father_id' => 3
			),
			'Mother' => array(
				'id' => 2,
				'name' => 'mother',
				'mother_id' => 4,
				'father_id' => 5,
				'Mother' => array(
					'id' => 4,
					'name' => 'mother - grand mother',
					'mother_id' => 0,
					'father_id' => 0,
					'Mother' => array(),
					'Father' => array()),
				'Father' => array(
					'id' => 5,
					'name' => 'mother - grand father',
					'mother_id' => 0,
					'father_id' => 0,
					'Father' => array(),
					'Mother' => array()
			)),
			'Father' => array(
				'id' => 3,
				'name' => 'father',
				'mother_id' => 6,
				'father_id' => 7,
				'Father' => array(
					'id' => 7,
					'name' => 'father - grand father',
					'mother_id' => 0,
					'father_id' => 0,
					'Father' => array(),
					'Mother' => array()
				),
				'Mother' => array(
					'id' => 6,
					'name' => 'father - grand mother',
					'mother_id' => 0,
					'father_id' => 0,
					'Mother' => array(),
					'Father' => array()
		)));

		$this->assertEquals($expected, $result);
	}

/**
 * testDynamicAssociations method
 *
 * @return void
 */
	public function testDynamicAssociations() {
		$this->loadFixtures('Article', 'Comment');
		$TestModel = new Article();

		$TestModel->belongsTo = $TestModel->hasAndBelongsToMany = $TestModel->hasOne = array();
		$TestModel->hasMany['Comment'] = array_merge($TestModel->hasMany['Comment'], array(
			'foreignKey' => false,
			'conditions' => array('Comment.user_id =' => '2')
		));
		$result = $TestModel->find('all');
		$expected = array(
			array(
				'Article' => array(
					'id' => '1',
					'user_id' => '1',
					'title' => 'First Article',
					'body' => 'First Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:39:23',
					'updated' => '2007-03-18 10:41:31'
				),
				'Comment' => array(
					array(
						'id' => '1',
						'article_id' => '1',
						'user_id' => '2',
						'comment' => 'First Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:45:23',
						'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => '6',
						'article_id' => '2',
						'user_id' => '2',
						'comment' => 'Second Comment for Second Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:55:23',
						'updated' => '2007-03-18 10:57:31'
			))),
			array(
				'Article' => array(
					'id' => '2',
					'user_id' => '3',
					'title' => 'Second Article',
					'body' => 'Second Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:41:23',
					'updated' => '2007-03-18 10:43:31'
				),
				'Comment' => array(
					array(
						'id' => '1',
						'article_id' => '1',
						'user_id' => '2',
						'comment' => 'First Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:45:23',
						'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => '6',
						'article_id' => '2',
						'user_id' => '2',
						'comment' => 'Second Comment for Second Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:55:23',
						'updated' => '2007-03-18 10:57:31'
			))),
			array(
				'Article' => array(
					'id' => '3',
					'user_id' => '1',
					'title' => 'Third Article',
					'body' => 'Third Article Body',
					'published' => 'Y',
					'created' => '2007-03-18 10:43:23',
					'updated' => '2007-03-18 10:45:31'
				),
				'Comment' => array(
					array(
						'id' => '1',
						'article_id' => '1',
						'user_id' => '2',
						'comment' => 'First Comment for First Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:45:23',
						'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => '6',
						'article_id' => '2',
						'user_id' => '2',
						'comment' => 'Second Comment for Second Article',
						'published' => 'Y',
						'created' => '2007-03-18 10:55:23',
						'updated' => '2007-03-18 10:57:31'
		))));

		$this->assertEquals($expected, $result);
	}

/**
 * testCreation method
 *
 * @return void
 */
	public function testCreation() {
		$this->loadFixtures('Article', 'ArticleFeaturedsTags', 'User', 'Featured');
		$TestModel = new Test();
		$result = $TestModel->create();
		$expected = array('Test' => array('notes' => 'write some notes here'));
		$this->assertEquals($expected, $result);
		$TestModel = new User();
		$result = $TestModel->schema();

		if (isset($this->db->columns['primary_key']['length'])) {
			$intLength = $this->db->columns['primary_key']['length'];
		} elseif (isset($this->db->columns['integer']['length'])) {
			$intLength = $this->db->columns['integer']['length'];
		} else {
			$intLength = 11;
		}
		foreach (array('collate', 'charset', 'comment') as $type) {
			foreach ($result as $i => $r) {
				unset($result[$i][$type]);
			}
		}

		$expected = array(
			'id' => array(
				'type' => 'integer',
				'null' => false,
				'default' => null,
				'length' => $intLength,
				'key' => 'primary'
			),
			'user' => array(
				'type' => 'string',
				'null' => false,
				'default' => '',
				'length' => 255
			),
			'password' => array(
				'type' => 'string',
				'null' => false,
				'default' => '',
				'length' => 255
			),
			'created' => array(
				'type' => 'datetime',
				'null' => true,
				'default' => null,
				'length' => null
			),
			'updated' => array(
				'type' => 'datetime',
				'null' => true,
				'default' => null,
				'length' => null
		));

		$this->assertEquals($expected, $result);

		$TestModel = new Article();
		$result = $TestModel->create();
		$expected = array('Article' => array('published' => 'N'));
		$this->assertEquals($expected, $result);

		$FeaturedModel = new Featured();
		$data = array(
			'article_featured_id' => 1,
			'category_id' => 1,
			'published_date' => array(
				'year' => 2008,
				'month' => 06,
				'day' => 11
			),
			'end_date' => array(
				'year' => 2008,
				'month' => 06,
				'day' => 20
		));

		$expected = array(
			'Featured' => array(
				'article_featured_id' => 1,
				'category_id' => 1,
				'published_date' => '2008-06-11 00:00:00',
				'end_date' => '2008-06-20 00:00:00'
		));

		$this->assertEquals($FeaturedModel->create($data), $expected);

		$data = array(
			'published_date' => array(
				'year' => 2008,
				'month' => 06,
				'day' => 11
			),
			'end_date' => array(
				'year' => 2008,
				'month' => 06,
				'day' => 20
			),
			'article_featured_id' => 1,
			'category_id' => 1
		);

		$expected = array(
			'Featured' => array(
				'published_date' => '2008-06-11 00:00:00',
				'end_date' => '2008-06-20 00:00:00',
				'article_featured_id' => 1,
				'category_id' => 1
		));

		$this->assertEquals($FeaturedModel->create($data), $expected);
	}

/**
 * testEscapeField to prove it escapes the field well even when it has part of the alias on it
 *
 * @return void
 */
	public function testEscapeField() {
		$TestModel = new Test();
		$db = $TestModel->getDataSource();

		$result = $TestModel->escapeField('test_field');
		$expected = $db->name('Test.test_field');
		$this->assertEquals($expected, $result);

		$result = $TestModel->escapeField('TestField');
		$expected = $db->name('Test.TestField');
		$this->assertEquals($expected, $result);

		$result = $TestModel->escapeField('DomainHandle', 'Domain');
		$expected = $db->name('Domain.DomainHandle');
		$this->assertEquals($expected, $result);

		ConnectionManager::create('mock', array('datasource' => 'DboMock'));
		$TestModel->setDataSource('mock');
		$db = $TestModel->getDataSource();

		$result = $TestModel->escapeField('DomainHandle', 'Domain');
		$expected = $db->name('Domain.DomainHandle');
		$this->assertEquals($expected, $result);
		ConnectionManager::drop('mock');
	}

/**
 * testGetID
 *
 * @return void
 */
	public function testGetID() {
		$TestModel = new Test();

		$result = $TestModel->getID();
		$this->assertFalse($result);

		$TestModel->id = 9;
		$result = $TestModel->getID();
		$this->assertEquals(9, $result);

		$TestModel->id = array(10, 9, 8, 7);
		$result = $TestModel->getID(2);
		$this->assertEquals(8, $result);

		$TestModel->id = array(array(), 1, 2, 3);
		$result = $TestModel->getID();
		$this->assertFalse($result);
	}

/**
 * test that model->hasMethod checks self and behaviors.
 *
 * @return void
 */
	public function testHasMethod() {
		$Article = new Article();
		$Article->Behaviors = $this->getMock('BehaviorCollection');

		$Article->Behaviors->expects($this->at(0))
			->method('hasMethod')
			->will($this->returnValue(true));

		$Article->Behaviors->expects($this->at(1))
			->method('hasMethod')
			->will($this->returnValue(false));

		$this->assertTrue($Article->hasMethod('find'));

		$this->assertTrue($Article->hasMethod('pass'));
		$this->assertFalse($Article->hasMethod('fail'));
	}

/**
 * Tests that tablePrefix is taken from the datasource if none is defined in the model
 *
 * @return void
 * @see http://cakephp.lighthouseapp.com/projects/42648/tickets/2277-caketestmodels-in-test-cases-do-not-set-model-tableprefix
 */
	public function testModelPrefixFromDatasource() {
		ConnectionManager::create('mock', array(
			'datasource' => 'DboMock',
			'prefix' => 'custom_prefix_'
		));
		$Article = new Article(false, null, 'mock');
		$this->assertEquals('custom_prefix_', $Article->tablePrefix);
		ConnectionManager::drop('mock');
	}

/**
 * Tests that calling schema() on a model that is not supposed to use a table
 * does not trigger any calls on any datasource
 *
 * @return void
 **/
	public function testSchemaNoDB() {
		$model = $this->getMock('Article', array('getDataSource'));
		$model->useTable = false;
		$model->expects($this->never())->method('getDataSource');
		$this->assertEmpty($model->schema());
	}
}
