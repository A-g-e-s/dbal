<?php declare(strict_types = 1);

namespace Nextras\Dbal\Drivers\Mysqli;


use mysqli_result;
use Nextras\Dbal\Exception\InvalidArgumentException;
use Nextras\Dbal\Exception\InvalidStateException;
use Nextras\Dbal\Result\IResultAdapter;
use Nextras\Dbal\Utils\StrictObjectTrait;


class MysqliResultAdapter implements IResultAdapter
{
	use StrictObjectTrait;


	/**
	 * @phpstan-param mysqli_result<array<mixed>> $result
	 */
	public function __construct(
		/**
		 * @phpstan-var mysqli_result<array<mixed>>
		 */
		private readonly mysqli_result $result,
		private readonly MysqliResultNormalizerFactory $normalizerFactory,
	)
	{
	}


	public function __destruct()
	{
		$this->result->free();
	}


	public function toBuffered(): IResultAdapter
	{
		return $this;
	}


	public function toUnbuffered(): IResultAdapter
	{
		return $this;
	}


	public function seek(int $index): void
	{
		if ($this->result->num_rows !== 0 && !$this->result->data_seek($index)) {
			throw new InvalidArgumentException("Unable to seek in row set to {$index} index.");
		}
	}


	public function fetch(): ?array
	{
		return $this->result->fetch_assoc();
	}


	public function getTypes(): array
	{
		$types = [];
		$count = $this->result->field_count;

		for ($i = 0; $i < $count; $i++) {
			$field = (array) $this->result->fetch_field_direct($i);
			$types[(string) $field['name']] = $field['type'];
		}

		return $types;
	}


	public function getRowsCount(): int
	{
		/** @phpstan-var int<0, max>|string $rows */
		$rows = $this->result->num_rows;
		if (is_string($rows)) { // @phpstan-ignore-line
			throw new InvalidStateException("Query returned more rows that Integer can store.");
		}
		return $rows;
	}


	public function getNormalizers(): array
	{
		return $this->normalizerFactory->resolve($this->getTypes());
	}
}
