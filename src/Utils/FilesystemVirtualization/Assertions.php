<?php

namespace Collector\Utils\FilesystemVirtualization;

use PHPUnit_Framework_Assert as PHPUnit;

trait Assertions
{

    /**
     * Get the virtualized path for a given path.
     *
     * @param $path
     * @return mixed
     */
    public function getPath($path)
    {
        return $path;
    }

    /**
     * Asserts a virtual directory has a given child.
     *
     * @param        $name
     * @param string $message
     */
    public function assertVfsHasChild($name, $message = '')
    {
        if (!is_array($name)) {
            $name = (array)$name;
        }

        foreach ($name as $actualName) {
            PHPUnit::assertTrue($this->vfs->hasChild($actualName), $message);
        }
    }

    /**
     * Asserts a virtual directory does not have a given child.
     *
     * @param        $name
     * @param string $message
     */
    public function assertVfsDoesNotHaveChild($name, $message = '')
    {
        if (!is_array($name)) {
            $name = (array)$name;
        }

        foreach ($name as $actualName) {
            PHPUnit::assertFalse($this->vfs->hasChild($actualName), $message);
        }
    }

    /**
     * Asserts that the contents of one vfs file is equal to the contents of another
     * vfs file.
     *
     * @param $expected
     * @param $actual
     * @param string $message
     * @param bool|false $canonicalize
     * @param bool|false $ignoreCase
     */
    public function assertVfsFileEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        PHPUnit::assertFileEquals($this->getPath($expected), $this->getPath($actual), $message, $canonicalize,
            $ignoreCase);
    }

    /**
     * Asserts that the contents of one vfs file is not equal to the contents of
     * another vfs file.
     *
     * @param $expected
     * @param $actual
     * @param string $message
     * @param bool|false $canonicalize
     * @param bool|false $ignoreCase
     */
    public function assertVfsFileNotEquals(
        $expected,
        $actual,
        $message = '',
        $canonicalize = false,
        $ignoreCase = false
    ) {
        PHPUnit::assertFileNotEquals($this->getPath($expected), $this->getPath($actual), $message, $canonicalize,
            $ignoreCase);
    }

    /**
     * Asserts that the contents of a string is equal
     * to the contents of a vfs file.
     *
     * @param $expectedFile
     * @param $actualString
     * @param string $message
     * @param bool|false $canonicalize
     * @param bool|false $ignoreCase
     */
    public function assertStringEqualsVfsFile(
        $expectedFile,
        $actualString,
        $message = '',
        $canonicalize = false,
        $ignoreCase = false
    ) {
        PHPUnit::assertStringEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
    }

    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a vfs file.
     *
     * @param $expectedFile
     * @param $actualString
     * @param string $message
     * @param bool|false $canonicalize
     * @param bool|false $ignoreCase
     */
    public function assertStringNotEqualsVfsFile(
        $expectedFile,
        $actualString,
        $message = '',
        $canonicalize = false,
        $ignoreCase = false
    ) {
        PHPUnit::assertStringNotEqualsFile($this->getPath($expectedFile), $actualString, $message, $canonicalize,
            $ignoreCase);
    }

    /**
     * Asserts that a vfs file exists.
     *
     * @param $filename
     * @param string $message
     */
    public function assertVfsFileExists($filename, $message = '')
    {
        PHPUnit::assertFileExists($this->getPath($filename), $message);
    }

    /**
     * Asserts that a vfs file does not exists.
     *
     * @param $filename
     * @param string $message
     */
    public function assertVfsFileNotExists($filename, $message = '')
    {
        PHPUnit::assertFileNotExists($this->getPath($filename), $message);
    }

    /**
     * Asserts that a string matches a given format file.
     *
     * @param $formatFile
     * @param $string
     * @param string $message
     */
    public function assertStringMatchesVfsFormatFile($formatFile, $string, $message = '')
    {
        PHPUnit::assertStringMatchesFormatFile($this->getPath($formatFile), $string, $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param $formatFile
     * @param $string
     * @param string $message
     */
    public function assertStringNotMatchesVfsFormatFile($formatFile, $string, $message = '')
    {
        PHPUnit::assertStringNotMatchesFormatFile($this->getPath($formatFile), $string, $message);
    }

    /**
     * Asserts that two vfs XML files are equal.
     *
     * @param $expectedFile
     * @param $actualFile
     * @param string $message
     */
    public function assertVfsXmlFileEqualsVfsXmlFile($expectedFile, $actualFile, $message = '')
    {
        PHPUnit::assertXmlFileEqualsXmlFile($this->getPath($expectedFile), $this->getPath($actualFile), $message);
    }

    /**
     * Asserts that two vfs XML files are not equal.
     *
     * @param $expectedFile
     * @param $actualFile
     * @param string $message
     */
    public function assertVfsXmlFileNotEqualsVfsXmlFile($expectedFile, $actualFile, $message = '')
    {
        PHPUnit::assertXmlFileNotEqualsXmlFile($this->getPath($expectedFile), $this->getPath($actualFile), $message);
    }

    /**
     * Asserts that two vfs XML documents are equal.
     *
     * @param $expectedFile
     * @param $actualXml
     * @param string $message
     */
    public function assertXmlStringEqualsVfsXmlFile($expectedFile, $actualXml, $message = '')
    {
        PHPUnit::assertXmlStringEqualsXmlFile($this->getPath($expectedFile), $actualXml, $message);
    }

    /**
     * Asserts that two vfs XML documents are not equal.
     *
     * @param $expectedFile
     * @param $actualXml
     * @param string $message
     */
    public function assertXmlStringNotEqualsVfsXmlFile($expectedFile, $actualXml, $message = '')
    {
        PHPUnit::assertXmlStringNotEqualsXmlFile($this->getPath($expectedFile), $actualXml, $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given vfs file are equal.
     *
     * @param $expectedFile
     * @param $actualJson
     * @param string $message
     */
    public function assertJsonStringEqualsVfsJsonFile($expectedFile, $actualJson, $message = '')
    {
        PHPUnit::assertJsonStringEqualsJsonFile($this->getPath($expectedFile), $actualJson, $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given vfs file are not equal.
     *
     * @param $expectedFile
     * @param $actualJson
     * @param string $message
     */
    public function assertJsonStringNotEqualsVfsJsonFile($expectedFile, $actualJson, $message = '')
    {
        PHPUnit::assertJsonStringNotEqualsJsonFile($this->getPath($expectedFile), $actualJson, $message);
    }

    /**
     *  Asserts that two vfs JSON files are equal.
     *
     * @param $expectedFile
     * @param $actualFile
     * @param string $message
     */
    public function assertVfsJsonFileEqualsVfsJsonFile($expectedFile, $actualFile, $message = '')
    {
        PHPUnit::assertJsonFileEqualsJsonFile($this->getPath($expectedFile), $this->getPath($actualFile), $message);
    }

    /**
     * Asserts that two vfs JSON files are not equal.
     *
     * @param $expectedFile
     * @param $actualFile
     * @param string $message
     */
    public function assertVfsJsonFileNotEqualsVfsJsonFile($expectedFile, $actualFile, $message = '')
    {
        PHPUnit::assertJsonFileNotEqualsJsonFile($this->getPath($expectedFile), $this->getPath($actualFile), $message);
    }

}