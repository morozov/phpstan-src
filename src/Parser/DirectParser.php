<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\ErrorHandler\Collecting;
use PhpParser\NodeTraverser;
use PHPStan\File\FileReader;

class DirectParser implements Parser
{

	private \PhpParser\Parser $parser;

	private \PhpParser\NodeTraverser $traverser;

	public function __construct(\PhpParser\Parser $parser, NodeTraverser $traverser)
	{
		$this->parser = $parser;
		$this->traverser = $traverser;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node\Stmt[]
	 */
	public function parseFile(string $file): array
	{
		return $this->parseString(FileReader::read($file));
	}

	/**
	 * @param string $sourceCode
	 * @return \PhpParser\Node\Stmt[]
	 */
	public function parseString(string $sourceCode): array
	{
		$errorHandler = new Collecting();
		$nodes = $this->parser->parse($sourceCode, $errorHandler);
		if ($errorHandler->hasErrors()) {
			throw new \PHPStan\Parser\ParserErrorsException($errorHandler->getErrors());
		}
		if ($nodes === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		/** @var array<\PhpParser\Node\Stmt> */
		return $this->traverser->traverse($nodes);
	}

}
