<?php

namespace Webqamdev\EncryptableFields\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Webqamdev\EncryptableFields\Console\Traits\FixEncryptTrait;
use Webqamdev\EncryptableFields\Models\Traits\EncryptableFields;
use Webqamdev\EncryptableFields\Services\DatabaseEncryption;

class FixMissingKeyCommand extends Command
{
    use FixEncryptTrait;

    protected const ARG_MODEL_CLASS = 'modelClass';

    protected const OPT_PROCESSED_FIELDS = 'processed-fields';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryptable-fields:fix-missing-key {' . self::ARG_MODEL_CLASS . '} '
    . '{--' . self::OPT_PROCESSED_FIELDS . '=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes data generated when encryption key was not set in .env';

    public function __construct()
    {
        parent::__construct();

        $this->databaseEncrypter = app()->make(DatabaseEncryption::class);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setModel();

        $this->setProcessedFields();

        $this->migrateEncryptions();

        return self::SUCCESS;
    }

    protected function setModel(): void
    {
        /** @var Model $modelClass */
        $modelClass = $this->argument(self::ARG_MODEL_CLASS);

        /** @var EncryptableFields $model */
        $this->model = $modelClass::getModel();

        if (!method_exists($this->model, 'isEncryptable')) {
            throw new \Exception(sprintf('%s is not encryptable.', $modelClass));
        }
    }

    protected function setProcessedFields(): void
    {
        $this->processedFields = $this->model->getEncryptableArray();

        $optionProcessFields = $this->option(self::OPT_PROCESSED_FIELDS);

        if (!empty($optionProcessFields)) {
            $this->processedFields = array_fill_keys(explode(',', $optionProcessFields), null);
        }
    }
}
