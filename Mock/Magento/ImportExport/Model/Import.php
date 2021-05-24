<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Import model
 *
 * @api
 *
 * @method string getBehavior() getBehavior()
 * @method self setEntity() setEntity(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Import extends \Magento\Framework\DataObject
{
    const BEHAVIOR_APPEND = 'append';
    const BEHAVIOR_ADD_UPDATE = 'add_update';
    const BEHAVIOR_REPLACE = 'replace';
    const BEHAVIOR_DELETE = 'delete';
    const BEHAVIOR_CUSTOM = 'custom';

    /**
     * Import source file.
     */
    const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * Import image archive.
     */
    const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**
     * Import images file directory.
     */
    const FIELD_NAME_IMG_FILE_DIR = 'import_images_file_dir';

    /**
     * Allowed errors count field name
     */
    const FIELD_NAME_ALLOWED_ERROR_COUNT = 'allowed_error_count';

    /**
     * Validation startegt field name
     */
    const FIELD_NAME_VALIDATION_STRATEGY = 'validation_strategy';

    /**
     * Import field separator.
     */
    const FIELD_FIELD_SEPARATOR = '_import_field_separator';

    /**
     * Import multiple value separator.
     */
    const FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR = '_import_multiple_value_separator';

    /**
     * Import empty attribute value constant.
     */
    const FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '_import_empty_attribute_value_constant';

    /**
     * Allow multiple values wrapping in double quotes for additional attributes.
     */
    const FIELDS_ENCLOSURE = 'fields_enclosure';

    /**
     * default delimiter for several values in one cell as default for FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR
     */
    const DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR = ',';

    /**
     * Import empty attribute default value
     */
    const DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '__EMPTY__VALUE__';
    const DEFAULT_SIZE = 50;
    const MAX_IMPORT_CHUNKS = 4;
    const IMPORT_HISTORY_DIR = 'import_history/';
    const IMPORT_DIR = 'import/';

    /**
     * Return operation result messages
     *
     * @param ProcessingErrorAggregatorInterface $validationResult
     * @return string[]
     * @throws LocalizedException
     */
    public function getOperationResultMessages(ProcessingErrorAggregatorInterface $validationResult)
    {
        return [];
    }

    /**
     * Get attribute type for upcoming validation.
     *
     * @param AbstractAttribute|Attribute $attribute
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getAttributeType(AbstractAttribute $attribute)
    {
        $frontendInput = $attribute->getFrontendInput();
        if ($attribute->usesSource() && in_array($frontendInput, ['select', 'multiselect', 'boolean'])) {
            return $frontendInput;
        } elseif ($attribute->isStatic()) {
            return $frontendInput == 'date' ? 'datetime' : 'varchar';
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * DB data source model getter.
     *
     * @return Data
     */
    public function getDataSourceModel()
    {
        return [];
    }

    /**
     * Default import behavior getter.
     *
     * @static
     * @return string
     */
    public static function getDefaultBehavior()
    {
        return self::BEHAVIOR_APPEND;
    }

    /**
     * Override standard entity getter.
     *
     * @throws LocalizedException
     * @return string
     */
    public function getEntity()
    {
        return 'product';
    }

    /**
     * Returns number of checked entities.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_getEntityAdapter()->getProcessedEntitiesCount();
    }

    /**
     * Returns number of checked rows.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getProcessedRowsCount()
    {
        return $this->_getEntityAdapter()->getProcessedRowsCount();
    }

    /**
     * Import/Export working directory (source files, result files, lock files etc.).
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->_varDirectory->getAbsolutePath('importexport/');
    }

    /**
     * Import source file structure to DB.
     *
     * @return bool
     * @throws LocalizedException
     */
    public function importSource()
    {
        return true;
    }
}
