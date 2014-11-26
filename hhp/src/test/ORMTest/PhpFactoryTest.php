<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\PhpFactory;
use orm\PhpPersistence;
use hfc\io\Directory;
use orm\Condition;
use orm\DescFactory;

class PhpFactoryTest extends AbstractTest {

	public function test () {
		$this->Instance();
		$this->getSaveDir();
		$this->setSaveDir();
		$this->getDataMapList();
		$this->filterResult();
	}

	public function Instance () {
		if (! PhpFactory::Instance() instanceof PhpFactory) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function getSaveDir () {
		$pp = PhpFactory::Instance();
		
		$pp->setSaveDir('bd');
		if ('bd' !== $pp->getSaveDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function setSaveDir () {
		$pp = PhpFactory::Instance();
		
		$pp->setSaveDir('bd');
		if ('bd' !== $pp->getSaveDir()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function getDataMapList () {
		$this->clearTestData();
		
		$g1 = new TestGroup();
		$g1->name = 'group1';
		
		$g2 = new TestGroup();
		$g2->name = 'group2';
		
		$pp = $this->createPhpPersistence();
		$pp->save($g1);
		$pp->save($g2);
		
		$factory = PhpFactory::Instance();
		$factory->setSaveDir($pp->getSaveDir());
		
		$arr = array(
			1 => array(
				'id' => 1,
				'name' => 'group1',
				'val_string' => 33,
				'val_float' => '3.1415'
			),
			2 => array(
				'id' => 2,
				'name' => 'group2',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$farr = $factory->getDataMapList(get_class($g1));
		if ($farr != $arr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$arr = array(
			2 => array(
				'id' => 2,
				'name' => 'group2',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$cond = new Condition('id=2');
		$farr = $factory->getDataMapList(get_class($g1), $cond);
		if ($farr != $arr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function filterResult () {
		$this->clearTestData();
		
		$pp = $this->createPhpPersistence();
		
		$this->clearTestData();
		
		$g1 = new TestGroup();
		$g1->name = 'group1';
		
		$g2 = new TestGroup();
		$g2->name = 'group2';
		
		$pp = $this->createPhpPersistence();
		$pp->save($g1);
		$pp->save($g2);
		
		$arrAll = array(
			1 => array(
				'id' => 1,
				'name' => 'group1',
				'val_string' => 33,
				'val_float' => '3.1415'
			),
			2 => array(
				'id' => 2,
				'name' => 'group2',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$arrGroup1 = array(
			1 => array(
				'id' => 1,
				'name' => 'group1',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$arrGroup2 = array(
			2 => array(
				'id' => 2,
				'name' => 'group2',
				'val_string' => 33,
				'val_float' => '3.1415'
			)
		);
		
		$clsName = get_class($g1);
		$clsDesc = DescFactory::Instance()->getDesc($clsName);
		
		$f = PhpFactory::Instance();
		$map = $f->getDataMapList($clsName);
		
		$cond = new Condition('id=2');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('id>1');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('id!=1');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		$cond = new Condition('id<>1');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('id<2');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('name like 2');
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('id=1');
		$cond->setRelationship(Condition::RELATIONSHIP_OR);
		$cond1 = new Condition('id=2');
		$cond1->setRelationship(Condition::RELATIONSHIP_AND);
		$cond1->add('name', '=', 'group2');
		$cond->addChild($cond1);
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrAll) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$cond = new Condition('id=1');
		$cond->setRelationship(Condition::RELATIONSHIP_OR);
		$cond1 = new Condition('id=2');
		$cond1->setRelationship(Condition::RELATIONSHIP_AND);
		$cond1->add('name', '=', 'group1');
		$cond->addChild($cond1);
		$filterArr = $f->filterResult($map, $clsDesc, $cond);
		if ($filterArr != $arrGroup1) {
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
}
?>