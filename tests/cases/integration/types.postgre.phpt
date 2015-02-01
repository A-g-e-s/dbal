<?php

/** @dataProvider? ../../databases.ini postgre */

namespace NextrasTests\Dbal;

use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class TypesPostgreTest extends IntegrationTestCase
{

	public function testBasics()
	{
		$result = $this->connection->query("
			SELECT
			-- driver specific
			'1 day 01:00:00'::interval,
			'0100'::bit(4),
			'100'::varbit,
			'YES'::bool,

			-- int
			'1'::int8,
			'2'::int4,
			'3'::int2,

			-- float
			'12.04'::numeric,
			'12.05'::float4,
			'12.06'::float8
		");

		$row = $result->fetch();
		Assert::equal(\DateInterval::createFromDateString('1 day 01:00:00'), $row->interval);
		Assert::equal(4, $row->bit);
		Assert::equal(4, $row->varbit);
		Assert::equal(TRUE, $row->bool);

		Assert::equal(1, $row->int8);
		Assert::equal(2, $row->int4);
		Assert::equal(3, $row->int2);

		Assert::equal(12.04, $row->numeric);
		Assert::equal(12.05, $row->float4);
		Assert::equal(12.06, $row->float8);
	}

}


$test = new TypesPostgreTest();
$test->run();
