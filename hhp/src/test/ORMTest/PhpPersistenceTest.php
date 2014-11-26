<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\DescFactory;
use orm\PhpPersistence;
use hfc\io\Directory;
use orm\Condition;

class PhpPersistenceTest extends AbstractTest {

	public function test () {
		$this->Instance();
		$this->getSaveDir();
		$this->setSaveDir();
		$this->arrayToCode();
		$this->mapToCode();
		$this->add();
		$this->update();
		$this->delete();
		$this->readToMap();
		$this->createSaveMap();
	}

	public function Instance () {
		if (! PhpPersistence::Instance() instanceof PhpPersistence) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function getSaveDir () {
		$pp = PhpPersistence::Instance();
		
		$pp->setSaveDir('bd');
		if ('bd' !== $pp->getSaveDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function setSaveDir () {
		$pp = PhpPersistence::Instance();
		
		$pp->setSaveDir('bd');
		if ('bd' !== $pp->getSaveDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function arrayToCode () {
		$this->mapToCode();
	}

	public function mapToCode () {
		$arr = array(
			'd\'d' => array(
				2,
				'3',
				'bb\'d""dd$aa"'
			),
			'ddf' => "\$arr",
			'abc' => true,
			'abd' => false,
			'abe' => null,
			array(
				array(
					array(
						'23',
						12
					)
				)
			)
		);
		$code = PhpPersistence::ArrayToCode($arr);
		$parsedArr = eval("return $code;");
		if ($parsedArr != $arr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function add () {
		$this->saveOneKey();
		$this->saveMultiKey();
	}

	public function update () {
		$this->add();
	}

	protected function saveMultiKey () {
		$this->clearTestData();
		
		$g2u = new TestGroup2User();
		$g2u->userId = 1;
		$g2u->groupId = 2;
		
		$pp = $this->createPhpPersistence();
		$dir = $pp->getSaveDir();
		
		// 添加
		$ret = $pp->add($g2u);
		if (- 1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$arr = array(
			'1,2' => array(
				'user_id' => 1,
				'group_id' => 2,
				'val' => ''
			)
		);
		
		$saveFileName = DescFactory::Instance()->getDesc(get_class($g2u))->persistentName;
		
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		// 修改
		$pp->save($g2u);
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function saveOneKey () {
		$this->clearTestData();
		
		$group = new TestGroup();
		$group->name = 'abc';
		
		$pp = $this->createPhpPersistence();
		$dir = $pp->getSaveDir();
		
		// 添加
		$ret = $pp->save($group);
		if (- 1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if (1 != $group->id) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$arr = array(
			1 => array(
				'id' => 1,
				'name' => 'abc',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$saveFileName = DescFactory::Instance()->getDesc(get_class($group))->persistentName;
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		// 修改
		$pp->save($group);
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function delete () {
		$pp = $this->createPhpPersistence();
		$dir = $pp->getSaveDir();
		
		$g2u = new TestGroup2User();
		
		$g2u->userId = 1;
		$g2u->groupId = 2;
		$pp->save($g2u);
		
		$g2u->userId = 2;
		$g2u->groupId = 2;
		$pp->save($g2u);
		
		$cond = new Condition('userId=1');
		$cond->add('groupId', '=', 2);
		$pp->delete(get_class($g2u), $cond);
		
		$arr = array(
			'2,2' => array(
				'user_id' => 2,
				'group_id' => 2,
				'val' => ''
			)
		);
		$saveFileName = DescFactory::Instance()->getDesc(get_class($g2u))->persistentName;
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		Directory::SClear($dir);
	}

	public function readToMap () {
		// 测试添加和删除都要用到，不再重复测试。
	}

	public function createSaveMap () {
		$this->createCommonMap();
		$this->createClassAttrMap();
	}

	/**
	 * 测试非类属性的转化
	 */
	protected function createCommonMap () {
		$this->clearTestData();
		
		// 犹豫FilterValue是其他类的功能，没有persistentName的情况，保存函数已经测试过，所以，这儿只需要测试autoIncrement
		$group = new TestGroup();
		$group->name = 'testCreateCommonMap';
		
		$group2 = new TestGroup();
		$group2->name = 'testCreateCommonMap2';
		
		$pp = $this->createPhpPersistence();
		$dir = $pp->getSaveDir();
		
		$pp->save($group);
		$gid1 = $group->id;
		$pp->save($group2);
		$gid2 = $group2->id;
		
		if ($gid2 != $gid1 + 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$arr = array(
			$gid1 => array(
				'id' => $gid1,
				'name' => 'testCreateCommonMap',
				'val_string' => 33,
				'val_float' => '3.1415'
			),
			$gid2 => array(
				'id' => $gid2,
				'name' => 'testCreateCommonMap2',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$saveFileName = DescFactory::Instance()->getDesc(get_class($group))->persistentName;
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($arr != $savedArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function createPhpPersistence () {
		$pp = PhpPersistence::Instance();
		$dir = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		$pp->setSaveDir($dir);
		
		$dir = $dir . 'sequence' . DIRECTORY_SEPARATOR;
		$pp->setSequenceDir($dir);
		
		if (! Directory::SExists($dir)) {
			Directory::SCreateAll($dir);
		}
		
		return $pp;
	}

	protected function clearTestData () {
		$dir = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		try {
			Directory::SUnlink($dir, true);
		} catch (\Exception $e) {
		}
	}

	public function setSequenceDir () {
		// createCommonMap里测试autoIncrement时已经测试过，不用再测。
	}

	/**
	 * 测试类属性的转换
	 */
	protected function createClassAttrMap () {
		$this->clearTestData();
		
		$g = new TestGroup();
		$g->name = 'group1';
		
		$u1 = new TestUser();
		$u1->name = 'user1';
		$u1->age = '34';
		$u1->amount = 1500.23;
		$u1->birthday = \DateTime::createFromFormat('Y-m-d', '2000-08-08');
		$u1->registerTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-10-20 22:22:22');
		$u1->female = true;
		
		$u2 = new TestUser();
		$u2->name = 'user2';
		$u2->age = '34';
		$u2->amount = 1500.23;
		$u2->birthday = \DateTime::createFromFormat('Y-m-d', '2000-08-08');
		$u2->registerTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-10-20 22:22:22');
		$u2->female = true;
		
		$g->userArr = array(
			$u1,
			$u2
		);
		
		$pp = $this->createPhpPersistence();
		$dir = $pp->getSaveDir();
		
		$pp->save($g, true);
		
		$arr = array(
			1 => array(
				'id' => 1,
				'name' => 'user1',
				'age' => 34,
				'amount' => 1500.23,
				'birthday' => '2000-08-08',
				'register_time' => '2014-10-20 22:22:22',
				'female' => true
			),
			2 => array(
				'id' => 2,
				'name' => 'user2',
				'age' => 34,
				'amount' => 1500.23,
				'birthday' => '2000-08-08',
				'register_time' => '2014-10-20 22:22:22',
				'female' => true
			)
		);
		$saveFileName = DescFactory::Instance()->getDesc(get_class($u1))->persistentName;
		$savedArr = $pp->Read2Map($saveFileName, $dir);
		if ($savedArr != $arr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>