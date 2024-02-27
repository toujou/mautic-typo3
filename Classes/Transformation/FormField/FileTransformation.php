<?php
declare(strict_types=1);

namespace Bitmotion\Mautic\Transformation\FormField;

use TYPO3\CMS\Core\Resource\MimeTypeDetector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileTransformation extends AbstractFormFieldTransformation
{
    private const DEFAULT_MAX_FILE_SIZE = 128;

    protected $type = 'file';


    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        parent::transform();

        $mimeTypeDetector = GeneralUtility::makeInstance(MimeTypeDetector::class);
        $allowedMimeTypes = $this->fieldDefinition['properties']['allowedMimeTypes'] ?? [];

        if (!empty($allowedMimeTypes)) {
            $fileExtensionsMap = array_map([$mimeTypeDetector, 'getFileExtensionsForMimeType'], $allowedMimeTypes);
            $allowedFileExtensions = array_merge([], ...$fileExtensionsMap);
        } else {
            $allowedFileExtensions = ['*'];
        }

        $this->fieldData['properties']['allowed_file_extensions'] = $allowedFileExtensions;
        $this->fieldData['properties']['allowed_file_size'] = self::DEFAULT_MAX_FILE_SIZE;

        if (isset($this->fieldDefinition['validators'])) {
            foreach ((array)$this->fieldDefinition['validators'] as $validator) {
                if (($validator['identifier'] ?? '') === 'FileSize' && ($validator['options']['maximum'] ?? null) ) {
                    $this->fieldData['properties']['allowed_file_size'] = preg_replace('/\D/', '', $validator['options']['maximum']);
                }
            }
        }
    }
}
