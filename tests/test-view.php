<?php

use Mockery as m;
use WPLibs\View\View;
use WPLibs\View\Engine;
use WPLibs\View\View_Factory;

class ViewTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testDataCanBeSetOnView() {
		$view = $this->getView();
		$view->with( 'foo', 'bar' );
		$view->with( [ 'baz' => 'boom' ] );
		$this->assertEquals( [ 'foo' => 'bar', 'baz' => 'boom' ], $view->get_data() );
	}

	public function testRenderProperlyRendersView() {
		$view = $this->getView( [ 'foo' => 'bar' ] );

		$view->get_factory()->shouldReceive( 'call_prepare' )->once()->with($view);
		$view->get_factory()->shouldReceive( 'get_shared' )->once()->andReturn( [ 'shared' => 'foo' ] );
		$view->get_engine()->shouldReceive( 'get' )->once()->with( 'path', [ 'foo' => 'bar', 'shared' => 'foo' ] )->andReturn( 'contents' );

		$callback = function ( View $rendered, $contents ) use ( $view ) {
			$this->assertEquals( $view, $rendered );
			$this->assertEquals( 'contents', $contents );
		};

		$this->assertEquals( 'contents', $view->render( $callback ) );
	}

	public function testRenderHandlingCallbackReturnValues() {
		$view = $this->getView();

		$view->get_factory()->shouldReceive( 'call_prepare' )->with($view);
		$view->get_factory()->shouldReceive( 'get_shared' )->andReturn( [ 'shared' => 'foo' ] );
		$view->get_engine()->shouldReceive( 'get' )->andReturn( 'contents' );

		$this->assertEquals( 'new contents', $view->render( function () {
			return 'new contents';
		} ) );

		$this->assertEmpty( $view->render( function () {
			return '';
		} ) );

		$this->assertEquals( 'contents', $view->render( function () {
			//
		} ) );
	}

	public function testViewNestBindsASubView() {
		$view = $this->getView();

		$view->get_factory()->shouldReceive( 'make' )->once()->with( 'foo', [ 'data' ] );
		$result = $view->nest( 'key', 'foo', [ 'data' ] );

		$this->assertInstanceOf( View::class, $result );
	}

	public function testViewGettersSetters() {
		$view = $this->getView( [ 'foo' => 'bar' ] );
		$this->assertEquals( $view->get_name(), 'view' );
		$this->assertEquals( $view->get_path(), 'path' );
		$data = $view->get_data();
		$this->assertEquals( $data['foo'], 'bar' );
		$view->set_path( 'newPath' );
		$this->assertEquals( $view->get_path(), 'newPath' );
	}

	public function testViewArrayAccess() {
		$view = $this->getView( [ 'foo' => 'bar' ] );
		$this->assertInstanceOf( ArrayAccess::class, $view );
		$this->assertTrue( $view->offsetExists( 'foo' ) );
		$this->assertEquals( $view->offsetGet( 'foo' ), 'bar' );
		$view->offsetSet( 'foo', 'baz' );
		$this->assertEquals( $view->offsetGet( 'foo' ), 'baz' );
		$view->offsetUnset( 'foo' );
		$this->assertFalse( $view->offsetExists( 'foo' ) );
	}

	public function testViewConstructedWithObjectData() {
		$view = $this->getView( new DataObjectStub );
		$this->assertInstanceOf( ArrayAccess::class, $view );
		$this->assertTrue( $view->offsetExists( 'foo' ) );
		$this->assertEquals( $view->offsetGet( 'foo' ), 'bar' );
		$view->offsetSet( 'foo', 'baz' );
		$this->assertEquals( $view->offsetGet( 'foo' ), 'baz' );
		$view->offsetUnset( 'foo' );
		$this->assertFalse( $view->offsetExists( 'foo' ) );
	}

	public function testViewMagicMethods() {
		$view = $this->getView( [ 'foo' => 'bar' ] );
		$this->assertTrue( isset( $view->foo ) );
		$this->assertEquals( $view->foo, 'bar' );
		$view->foo = 'baz';
		$this->assertEquals( $view->foo, 'baz' );
		$this->assertEquals( $view['foo'], $view->foo );
		unset( $view->foo );
		$this->assertFalse( isset( $view->foo ) );
		$this->assertFalse( $view->offsetExists( 'foo' ) );
	}

	protected function getView( $data = [] ) {
		return new View(
			m::mock( View_Factory::class ),
			m::mock( Engine::class ),
			'view',
			'path',
			$data
		);
	}
}

class DataObjectStub {
	public $foo = 'bar';
}
