<?php

namespace Webqamdev\EncryptableFields\Console\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Webqamdev\EncryptableFields\Models\Traits\EncryptableFields;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;

/**
 * @property
 */
trait FixEncryptTrait
{
    /**
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * @var Model|EncryptableFields
     */
    protected $model;

    /**
     * @var array
     */
    protected $processedFields;

    /**
     * @var DatabaseEncryption
     */
    protected $databaseEncrypter;

    /**
     * @var string[]
     */
    protected $defaultSelectFields = ['id'];

    public function getSelectFields(): Collection
    {
        $fields = collect($this->defaultSelectFields);
        foreach ($this->processedFields as $field => $hashField) {
            $fields->push($field);
            if (null !== $hashField) {
                $fields->push($hashField);
            }
        }

        return $fields;
    }

    /**
     * @param Model|EncryptableFields $entity
     */
    protected function getNewEncryptedFields(Model $entity): array
    {
        $newFields = [];

        foreach ($this->processedFields as $field => $hashField) {
            $value = $entity->$field;

            if (method_exists($entity, 'isHashable') && $entity->isHashable($field)) {
                $newFields[$hashField] = null;
                if (!empty($value)) {
                    $newFields[$hashField] = dbHashValue($value);
                }
            }

            $newFields[$field] = null;
            if (!empty($value)) {
                $newFields[$field] = $this->databaseEncrypter->encrypt($value);
            }
        }

        return $newFields;
    }

    protected function getQuery(): Builder
    {
        return $this->model::query()->select($this->getSelectFields()->toArray());
    }

    protected function migrateEncryptions(): void
    {
        $query = $this->getQuery();

        $this->withProgressBar($query->count(), function (ProgressBar $bar) use ($query) {
            $query
                ->chunk($this->chunkSize, function (Collection $entities) use ($bar) {
                    DB::beginTransaction();

                    $entities->each(function (Model $entity) use ($bar) {
                        $newEncryptedFields = $this->getNewEncryptedFields($entity);

                        DB::table($entity->getTable())
                            ->where('id', $entity->id)
                            ->update($newEncryptedFields);

                        $bar->advance();
                    });

                    DB::commit();
                });
        });
    }
}
