<?php


namespace NazmulIslam\Utility\Core\Traits;

use NazmulIslam\Utility\Logger\Logger;
use NazmulIslam\Utility\Utility\Utility;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Capsule\Manager as DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use Spatie\LaravelCipherSweet\Observers\ModelObserver;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait UsesCipherSweetTrait
{
    public static EncryptedRow $cipherSweetEncryptedRow;

    protected static function bootUsesCipherSweet()
    {
        static::observe(ModelObserver::class);

        $key = CIPHER_SWEET_KEY;

        if (Utility::tenantEncryptionKey() != null) {
            $key = Utility::tenantEncryptionKey();
        } elseif (getenv('CIPHER_SWEET_KEY')) {
            $key = getenv('CIPHER_SWEET_KEY');
        }

        $keyProvider = new StringProvider($key);

        $cipherSweetEngine = new CipherSweetEngine($keyProvider);

        static::$cipherSweetEncryptedRow = new EncryptedRow(
            $cipherSweetEngine,
            (new static())->getTable()
        );

        static::configureCipherSweet(static::$cipherSweetEncryptedRow);
    }

    abstract public static function configureCipherSweet(EncryptedRow $encryptedRow): void;

    /**
     * @return void
     * @throws \ParagonIE\CipherSweet\Exception\ArrayKeyException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function encryptRow(): void
    {
        $fieldsToEncrypt = static::$cipherSweetEncryptedRow->listEncryptedFields();

        $attributes = $this->getAttributes();

        foreach ($fieldsToEncrypt as $field) {
            $attributes[$field] ??= '';
        }

        $this->setRawAttributes(static::$cipherSweetEncryptedRow->encryptRow($attributes));
    }

    public function updateBlindIndexes(): void
    {
        foreach (static::$cipherSweetEncryptedRow->getAllBlindIndexes($this->getAttributes()) as $name => $blindIndex) {
            DB::connection('app')->table('blind_indexes')->upsert([
                'value' => $blindIndex,
                'indexable_type' => $this->getMorphClass(),
                'indexable_id' => $this->getKey(),
                'name' => $name,
            ], [
                'indexable_type',
                'indexable_id',
                'name',
            ]);
        }
    }

    public function deleteBlindIndexes(): void
    {
        DB::connection('app')->table('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->where('indexable_id', $this->getKey())
            ->delete();
    }

    public function decryptRow(): void
    {
        $this->setRawAttributes(static::$cipherSweetEncryptedRow->setPermitEmpty(true)->decryptRow($this->getAttributes()), true);
    }

    public function scopeWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->whereExists(fn (Builder $query): Builder => $this->buildBlindQuery($query, $column, $indexName, $value));
    }

    public function scopeOrWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->orWhereExists(fn (Builder $query): Builder => $this->buildBlindQuery($query, $column, $indexName, $value));
    }

    private function buildBlindQuery(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->select(DB::connection('app')->raw(1))
            ->from('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->where('indexable_id', DB::connection('app')->raw($this->getTable() . '.' . $this->getKeyName()))
            ->where('name', $indexName)
            ->where('value', static::$cipherSweetEncryptedRow->getBlindIndex($indexName, [$column => $value]));
    }
}
