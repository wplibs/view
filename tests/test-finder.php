<?php

use Mockery as m;
use WPLibs\View\File_Finder;

class FinderTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();
		m::close();
	}

	public function testBasicViewFinding() {
		$finder = $this->getFinder();

		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.php' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/foo.php', $finder->find( 'foo' ) );
	}

	public function testCascadingFileLoading() {
		$finder = $this->getFinder();
		$finder->add_extension( 'twig' );

		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.twig' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.php' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/foo.php', $finder->find( 'foo' ) );
	}

	public function testDirectoryCascadingFileLoading() {
		$finder = $this->getFinder();
		$finder->add_extension( 'twig' );

		$finder->add_location( __DIR__ . '/nested' );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.twig' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.php' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/nested/foo.twig' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/nested/foo.twig', $finder->find( 'foo' ) );
	}

	public function testNamespacedBasicFileLoading() {
		$finder = $this->getFinder();

		$finder->add_namespace( 'foo', __DIR__ . '/foo' );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo/bar/baz.php' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/foo/bar/baz.php', $finder->find( 'foo::bar.baz' ) );
	}

	public function testCascadingNamespacedFileLoading() {
		$finder = $this->getFinder();

		$finder->add_namespace( 'foo', __DIR__ . '/foo' );
		$finder->add_extension( 'twig' );

		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo/bar/baz.twig' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo/bar/baz.php' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/foo/bar/baz.php', $finder->find( 'foo::bar.baz' ) );
	}

	public function testDirectoryCascadingNamespacedFileLoading() {
		$finder = $this->getFinder();
		$finder->add_extension( 'twig' );
		$finder->add_namespace( 'foo', [ __DIR__ . '/foo', __DIR__ . '/bar' ] );

		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo/bar/baz.twig' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo/bar/baz.php' )->andReturn( false );
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/bar/bar/baz.twig' )->andReturn( true );

		$this->assertEquals( __DIR__ . '/bar/bar/baz.twig', $finder->find( 'foo::bar.baz' ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownWhenViewNotFound() {
		$finder = $this->getFinder();
		$finder->get_filesystem()->shouldReceive( 'exists' )->once()->with( __DIR__ . '/foo.php' )->andReturn( false );
		$finder->find( 'foo' );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownOnInvalidViewName() {
		$finder = $this->getFinder();
		$finder->find( 'name::' );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testExceptionThrownWhenNoHintPathIsRegistered() {
		$finder = $this->getFinder();
		$finder->find( 'name::foo' );
	}

	public function testAddingExtensionPrependsNotAppends() {
		$finder = $this->getFinder();
		$finder->add_extension( 'baz' );
		$extensions = $finder->get_extensions();
		$this->assertEquals( 'baz', reset( $extensions ) );
	}

	public function testAddingExtensionsReplacesOldOnes() {
		$finder = $this->getFinder();

		$finder->add_extension( 'baz' );
		$finder->add_extension( 'baz' );

		$this->assertCount( 2, $finder->get_extensions() );
	}

	public function testPassingViewWithHintReturnsTrue() {
		$finder = $this->getFinder();

		$this->assertTrue( $finder->has_hint_information( 'hint::foo.bar' ) );
	}

	public function testPassingViewWithoutHintReturnsFalse() {
		$finder = $this->getFinder();

		$this->assertFalse( $finder->has_hint_information( 'foo.bar' ) );
	}

	public function testPassingViewWithFalseHintReturnsFalse() {
		$finder = $this->getFinder();

		$this->assertFalse( $finder->has_hint_information( '::foo.bar' ) );
	}

	protected function getFinder() {
		return new File_Finder( [ __DIR__ ], null, m::mock( 'WP_Filesystem_Base' ) );
	}
}
