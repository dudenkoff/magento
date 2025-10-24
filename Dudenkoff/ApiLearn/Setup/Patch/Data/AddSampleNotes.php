<?php
/**
 * Add Sample Notes Data Patch
 * 
 * WHAT IS A DATA PATCH?
 * Data patches insert/update data in database.
 * They run once and are tracked in patch_list table.
 * 
 * KEY CONCEPTS:
 * 1. Implements DataPatchInterface
 * 2. apply() - Main logic
 * 3. getDependencies() - Other patches that must run first
 * 4. getAliases() - Previous patch names (for migration)
 * 5. Automatically tracked - won't run twice
 * 
 * WHEN TO USE:
 * - Initial data population
 * - Default configurations
 * - Sample data for testing
 * - Migration data
 * 
 * DATA PATCH vs SCHEMA PATCH:
 * - Data Patch: Insert/update DATA
 * - Schema Patch: Modify table STRUCTURE (columns, indexes)
 */

namespace Dudenkoff\ApiLearn\Setup\Patch\Data;

use Dudenkoff\ApiLearn\Api\Data\NoteInterfaceFactory;
use Dudenkoff\ApiLearn\Api\NoteRepositoryInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddSampleNotes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var NoteInterfaceFactory
     */
    private $noteFactory;

    /**
     * @var NoteRepositoryInterface
     */
    private $noteRepository;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param NoteInterfaceFactory $noteFactory
     * @param NoteRepositoryInterface $noteRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        NoteInterfaceFactory $noteFactory,
        NoteRepositoryInterface $noteRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->noteFactory = $noteFactory;
        $this->noteRepository = $noteRepository;
    }

    /**
     * Apply patch - Insert sample notes
     * 
     * This runs once when you execute setup:upgrade.
     * Magento tracks it in patch_list table.
     *
     * @return void
     */
    public function apply()
    {
        // Start setup
        $this->moduleDataSetup->startSetup();

        // Sample notes data
        $sampleNotes = [
            [
                'title' => 'Welcome to Magento 2 API',
                'content' => 'This is a sample note created by the data patch. It demonstrates how to use Magento 2 Web APIs with real database persistence.',
                'author' => 'System',
                'is_published' => true
            ],
            [
                'title' => 'Understanding Service Contracts',
                'content' => 'Service contracts are interfaces that define how modules communicate. They include data interfaces, repository interfaces, and management interfaces.',
                'author' => 'Dudenkoff',
                'is_published' => true
            ],
            [
                'title' => 'Repository Pattern Explained',
                'content' => 'The repository pattern provides a gateway to data persistence. It handles CRUD operations: save, getById, getList, delete, deleteById.',
                'author' => 'Dudenkoff',
                'is_published' => false
            ],
            [
                'title' => 'Search Criteria in Action',
                'content' => 'SearchCriteria allows powerful filtering, sorting, and pagination. Use it to query data flexibly without writing SQL.',
                'author' => 'Magento Expert',
                'is_published' => true
            ],
            [
                'title' => 'ACL Security Best Practices',
                'content' => 'Always protect sensitive endpoints with ACL resources. Define granular permissions for view, create, edit, delete operations.',
                'author' => 'Security Team',
                'is_published' => true
            ],
            [
                'title' => 'Draft Note - Not Published',
                'content' => 'This note is in draft status. It demonstrates filtering by is_published field in search criteria.',
                'author' => 'Dudenkoff',
                'is_published' => false
            ],
            [
                'title' => 'Testing REST APIs',
                'content' => 'Use curl, Postman, or any HTTP client to test your APIs. Start with public endpoints, then test protected ones with tokens.',
                'author' => 'QA Team',
                'is_published' => true
            ],
            [
                'title' => 'Data Patches vs Schema Patches',
                'content' => 'Data patches insert/update data. Schema patches modify table structure. Both are tracked and run only once.',
                'author' => 'DevOps',
                'is_published' => false
            ],
            [
                'title' => 'Web API Performance Tips',
                'content' => 'Use pagination for large datasets. Add database indexes on frequently queried fields. Cache results when possible.',
                'author' => 'Performance Team',
                'is_published' => true
            ],
            [
                'title' => 'Integration Testing',
                'content' => 'Test your APIs with integration tests. Mock dependencies. Verify responses. Check error handling.',
                'author' => 'Test Engineer',
                'is_published' => true
            ]
        ];

        // Insert sample notes
        foreach ($sampleNotes as $noteData) {
            try {
                $note = $this->noteFactory->create();
                $note->setTitle($noteData['title']);
                $note->setContent($noteData['content']);
                $note->setAuthor($noteData['author']);
                $note->setIsPublished($noteData['is_published']);
                
                $this->noteRepository->save($note);
            } catch (\Exception $e) {
                // Log error but continue with other notes
                // In production: use proper logging
            }
        }

        // End setup
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get dependencies
     * 
     * Returns array of patch class names that must run before this one.
     * Empty array = no dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     * 
     * Returns array of previous patch class names (for backward compatibility).
     * Used when renaming patches.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}

