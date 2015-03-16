<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Console\Test\Prompter;

use Windwalker\Console\Prompter\ValidatePrompter;

/**
 * Class ValidatePrompterTest
 *
 * @since  2.0
 */
class ValidatePrompterTest extends AbstractPrompterTest
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 *
	 * @since  2.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = $prompter = new ValidatePrompter('Tell me something: ', array('flower', 'sakura', 'rose'), null, $this->io);
	}

	/**
	 * Test prompter ask.
	 *
	 * @return  void
	 *
	 * @since  2.0
	 */
	public function testAsk()
	{
		$this->setStream('sakura');

		$this->assertEquals($this->instance->ask('Tell me something: '), 'sakura', 'Should validate success and pass.');
	}
}
