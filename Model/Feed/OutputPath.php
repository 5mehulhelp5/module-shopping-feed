<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use MageOS\ShoppingFeed\Model\Feed;

class OutputPath
{
    public const CONFIGURED_BASE_DIRECTORY = 'pub/media/mageos-shopping-feed';

    private const MEDIA_BASE_DIRECTORY = 'mageos-shopping-feed';

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;

    /**
     * @var WriteInterface
     */
    private WriteInterface $logDirectory;

    /**
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     */
    public function __construct(Filesystem $filesystem, DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->logDirectory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
    }

    /**
     * Reject a feed whose configured feed, log or promotion file paths are not allowed.
     *
     * @param Feed $feed
     * @return void
     * @throws LocalizedException
     */
    public function validateFeed(Feed $feed): void
    {
        $directory = (string) $feed->getConfig('general_feed_dir');
        $this->validatePath($directory, (string) $feed->getConfig('file_feed'), ['txt', 'csv', 'tsv', 'xml']);
        $this->validateFilename((string) $feed->getConfig('file_log'), ['log']);

        $promotionTemplate = (string) $feed->getConfig('file_promotion');
        if ($promotionTemplate !== '') {
            $this->validatePath($directory, $promotionTemplate, ['txt', 'csv', 'tsv', 'xml']);
        }
    }

    /**
     * Feed output file path, absolute or relative to the Magento root.
     *
     * @param Feed $feed
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    public function getFeedFile(Feed $feed, bool $absolute = true): string
    {
        return $this->resolveFeedFile(
            (string) $feed->getConfig('general_feed_dir'),
            (string) $feed->getConfig('file_feed'),
            (int) $feed->getId(),
            $absolute
        );
    }

    /**
     * Feed log file path inside var/log, absolute or relative to the Magento root.
     *
     * @param Feed $feed
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    public function getLogFile(Feed $feed, bool $absolute = true): string
    {
        return $this->resolveLogFile((string) $feed->getConfig('file_log'), (int) $feed->getId(), $absolute);
    }

    /**
     * Promotions output file path, absolute or relative to the Magento root.
     *
     * @param Feed $feed
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    public function getPromotionFile(Feed $feed, bool $absolute = true): string
    {
        return $this->resolvePath(
            (string) $feed->getConfig('general_feed_dir'),
            (string) $feed->getConfig('file_promotion'),
            (int) $feed->getId(),
            ['txt', 'csv', 'tsv', 'xml'],
            $absolute
        );
    }

    /**
     * Resolve a feed file inside the configured media output directory.
     *
     * @param string $configuredDirectory
     * @param string $filenameTemplate
     * @param int $feedId
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    public function resolveFeedFile(
        string $configuredDirectory,
        string $filenameTemplate,
        int $feedId,
        bool $absolute = true
    ): string {
        return $this->resolvePath(
            $configuredDirectory,
            $filenameTemplate,
            $feedId,
            ['txt', 'csv', 'tsv', 'xml'],
            $absolute
        );
    }

    /**
     * Resolve a log file inside var/log, creating the directory when needed.
     *
     * @param string $filenameTemplate
     * @param int $feedId
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    public function resolveLogFile(string $filenameTemplate, int $feedId, bool $absolute = true): string
    {
        $filename = $this->validateFilename($filenameTemplate, ['log'], $feedId);
        $this->logDirectory->create();

        $driver = $this->logDirectory->getDriver();
        $logRoot = $driver->getRealPath($this->directoryList->getPath(DirectoryList::LOG));
        if ($logRoot === false) {
            throw new LocalizedException(__('The Magento log directory could not be resolved.'));
        }

        $absolutePath = $this->logDirectory->getAbsolutePath($filename);
        $this->assertExistingPathInsideDirectory($absolutePath, $logRoot, $driver);

        return $absolute ? $absolutePath : $this->makeRootRelative($absolutePath);
    }

    /**
     * Validate, create and contain an output path under the media directory.
     *
     * @param string $configuredDirectory
     * @param string $filenameTemplate
     * @param int $feedId
     * @param array $allowedExtensions
     * @param bool $absolute
     * @return string
     * @throws LocalizedException
     */
    private function resolvePath(
        string $configuredDirectory,
        string $filenameTemplate,
        int $feedId,
        array $allowedExtensions,
        bool $absolute
    ): string {
        [$mediaRelativeDirectory, $filename] = $this->validatePath(
            $configuredDirectory,
            $filenameTemplate,
            $allowedExtensions,
            $feedId
        );

        $this->mediaDirectory->create($mediaRelativeDirectory);
        $this->assertDirectoryRemainsInsideMedia($mediaRelativeDirectory);

        $absolutePath = $this->mediaDirectory->getAbsolutePath($mediaRelativeDirectory . '/' . $filename);
        $this->assertExistingPathRemainsInsideOutputDirectory($absolutePath, $mediaRelativeDirectory);

        if ($absolute) {
            return $absolutePath;
        }

        return $this->makeRootRelative($absolutePath);
    }

    /**
     * Validate the configured directory and filename; return the media-relative directory and filename.
     *
     * @param string $configuredDirectory
     * @param string $filenameTemplate
     * @param array $allowedExtensions
     * @param int|null $feedId
     * @return array
     * @throws LocalizedException
     */
    private function validatePath(
        string $configuredDirectory,
        string $filenameTemplate,
        array $allowedExtensions,
        ?int $feedId = null
    ): array {
        $directory = trim(str_replace('\\', '/', $configuredDirectory), '/');
        if ($directory !== self::CONFIGURED_BASE_DIRECTORY
            && strncmp(
                $directory,
                self::CONFIGURED_BASE_DIRECTORY . '/',
                strlen(self::CONFIGURED_BASE_DIRECTORY) + 1
            ) !== 0
        ) {
            throw new LocalizedException(
                __('Feed output must be inside %1.', self::CONFIGURED_BASE_DIRECTORY)
            );
        }

        $subdirectory = substr($directory, strlen(self::CONFIGURED_BASE_DIRECTORY));
        if ($subdirectory !== '' && !preg_match('#^/[A-Za-z0-9._-]+(?:/[A-Za-z0-9._-]+)*$#D', $subdirectory)) {
            throw new LocalizedException(__('The feed output directory contains an invalid path segment.'));
        }

        foreach (explode('/', ltrim($subdirectory, '/')) as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new LocalizedException(__('The feed output directory contains an invalid path segment.'));
            }
        }

        $filename = $this->validateFilename($filenameTemplate, $allowedExtensions, $feedId);

        $mediaRelativeDirectory = self::MEDIA_BASE_DIRECTORY . $subdirectory;
        return [$mediaRelativeDirectory, $filename];
    }

    /**
     * Reject an output directory that resolves outside pub/media.
     *
     * @param string $mediaRelativeDirectory
     * @return void
     * @throws LocalizedException
     */
    private function assertDirectoryRemainsInsideMedia(string $mediaRelativeDirectory): void
    {
        $driver = $this->mediaDirectory->getDriver();
        $mediaRoot = $driver->getRealPath($this->directoryList->getPath(DirectoryList::MEDIA));
        $outputDirectory = $driver->getRealPath($this->mediaDirectory->getAbsolutePath($mediaRelativeDirectory));

        if ($mediaRoot === false || $outputDirectory === false) {
            throw new LocalizedException(__('The feed output directory could not be resolved.'));
        }

        $mediaPrefix = rtrim($mediaRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($outputDirectory !== rtrim($mediaRoot, DIRECTORY_SEPARATOR)
            && strncmp($outputDirectory, $mediaPrefix, strlen($mediaPrefix)) !== 0
        ) {
            throw new LocalizedException(__('The feed output directory resolves outside the media directory.'));
        }
    }

    /**
     * Reject an existing output file that resolves outside its output directory.
     *
     * @param string $absolutePath
     * @param string $mediaRelativeDirectory
     * @return void
     * @throws LocalizedException
     */
    private function assertExistingPathRemainsInsideOutputDirectory(
        string $absolutePath,
        string $mediaRelativeDirectory
    ): void {
        $driver = $this->mediaDirectory->getDriver();
        if (!$driver->isExists($absolutePath)) {
            return;
        }

        $outputDirectory = $driver->getRealPath($this->mediaDirectory->getAbsolutePath($mediaRelativeDirectory));
        if ($outputDirectory === false) {
            throw new LocalizedException(__('The feed output directory could not be resolved.'));
        }

        $this->assertExistingPathInsideDirectory($absolutePath, $outputDirectory, $driver);
    }

    /**
     * Expand the feed id into the filename template and check its characters and extension.
     *
     * @param string $filenameTemplate
     * @param array $allowedExtensions
     * @param int|null $feedId
     * @return string
     * @throws LocalizedException
     */
    private function validateFilename(
        string $filenameTemplate,
        array $allowedExtensions,
        ?int $feedId = null
    ): string {
        if (substr_count($filenameTemplate, '%s') > 1
            || strpos(str_replace('%s', '', $filenameTemplate), '%') !== false
        ) {
            throw new LocalizedException(__('The feed filename contains an invalid format token.'));
        }

        $filename = $feedId === null ? str_replace('%s', '1', $filenameTemplate) : sprintf($filenameTemplate, $feedId);
        $extensionPattern = implode('|', array_map(
            static fn (string $extension): string => preg_quote($extension, '#'),
            $allowedExtensions
        ));
        if (!preg_match('#^[A-Za-z0-9][A-Za-z0-9._-]*\.(?:' . $extensionPattern . ')$#D', $filename)) {
            throw new LocalizedException(__('The feed filename or extension is not allowed.'));
        }

        return $filename;
    }

    /**
     * Reject an existing path whose real path is outside the given directory.
     *
     * @param string $absolutePath
     * @param string $absoluteDirectory
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @return void
     * @throws LocalizedException
     */
    private function assertExistingPathInsideDirectory(
        string $absolutePath,
        string $absoluteDirectory,
        \Magento\Framework\Filesystem\DriverInterface $driver
    ): void {
        if (!$driver->isExists($absolutePath)) {
            return;
        }

        $resolvedPath = $driver->getRealPath($absolutePath);
        $directoryPrefix = rtrim($absoluteDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($resolvedPath === false || strncmp($resolvedPath, $directoryPrefix, strlen($directoryPrefix)) !== 0) {
            throw new LocalizedException(__('The configured file resolves outside its permitted directory.'));
        }
    }

    /**
     * Convert an absolute path under the Magento root to a root-relative path with forward slashes.
     *
     * @param string $absolutePath
     * @return string
     * @throws LocalizedException
     */
    private function makeRootRelative(string $absolutePath): string
    {
        $root = rtrim($this->directoryList->getRoot(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($absolutePath, $root, strlen($root)) !== 0) {
            throw new LocalizedException(__('The configured output path is outside the Magento root directory.'));
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolutePath, strlen($root)));
    }
}
