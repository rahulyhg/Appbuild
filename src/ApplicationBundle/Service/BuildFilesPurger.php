<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Handle purge build files tasks.
 */
class BuildFilesPurger
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $buildsApplicationDir;

    /**
     * @var string
     */
    private $unusedFilesFinderDateFilter;

    /**
     * @param Filesystem    $filesystem
     * @param EntityManager $entityManager
     * @param string        $buildsApplicationDir
     */
    public function __construct(
        Filesystem $filesystem,
        EntityManager $entityManager,
        $buildsApplicationDir
    ) {
        $this->filesystem = $filesystem;
        $this->entityManager = $entityManager;
        $this->buildsApplicationDir = $buildsApplicationDir;

        // Unused files finder date filter has a default value (!= null)
        // because we must let the time for user to submit the form after uploading the build file
        $this->unusedFilesFinderDateFilter = '< now - 12hours';
    }

    /**
     * @param $finderDateFilter
     *
     * @return $this
     */
    public function setUnusedFilesFinderDateFilter($finderDateFilter)
    {
        $this->unusedFilesFinderDateFilter = $finderDateFilter;

        return $this;
    }

    /**
     * Remove unused build files.
     */
    public function purge()
    {
        foreach ($this->getUnusedFiles() as $file) {
            $this->filesystem->remove($file);
        }
    }

    /**
     * Get unused files.
     *
     * @return Finder|\SplFileInfo[]
     */
    public function getUnusedFiles()
    {
        $usedFilesPath = $this->getUsedFilesPath();

        return (new Finder())
            ->files()
            ->in($this->buildsApplicationDir)
            ->filter(function (\SplFileInfo $file) use ($usedFilesPath) {
                return !in_array($file->getRealPath(), $usedFilesPath);
            })
           ->date($this->unusedFilesFinderDateFilter)
        ;
    }

    /**
     * Get used files.
     *
     * @return string[]
     */
    private function getUsedFilesPath()
    {
        $usedFiles = [];

        $builds = $this->entityManager->getRepository('AppbuildApplicationBundle:Build')->findAll();
        foreach ($builds as $build) {
            $usedFiles[] = $build->getFilePath();
        }

        return $usedFiles;
    }
}
