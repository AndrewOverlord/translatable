<?php

namespace Components\Translatable;

use Components\Translatable\TranslationFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * This is the translatable trait.
 * Based on Laravel Translator (vinkla/translator)
 */
trait Translatable
{


    /**
     * The translations cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Get a translation.
     *
     * @param string|null $locale
     * @param bool $fallback
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function translate($locale = null, $fallback = true)
    {

        $locale = $locale ?: $this->getLocale();

        $translation = $this->getTranslation($locale);


        if (!$translation && $fallback) {
            $translation = $this->getTranslation($this->getFallback());
        }

        if (!$translation && !$fallback) {
            $translation = $this->getEmptyTranslation($locale);
        }

        return $translation;
    }

    /**
     * Get a translation or create new.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    protected function translateOrNew($locale)
    {
        $translation = $this->getTranslation($locale);

        if (!$translation) {
            $translation = $this->translations()
                ->where('locale', $locale)
                ->first();

            if (!$translation) {
                $translation = $this->getTranslationInstance();
                $translation->locale = $locale;
            }
        }

        return $translation;
    }

    /**
     * Get a translation.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    protected function getTranslation($locale)
    {
        if (isset($this->cache[$locale])) {
            return $this->cache[$locale];
        }

        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            $this->cache[$locale] = $translation;
        }

        return $translation;
    }

    /**
     * Get an empty translation.
     *
     * @param string $locale
     *
     * @return mixed
     */
    protected function getEmptyTranslation($locale)
    {
        $appLocale = $this->getLocale();

        $this->setLocale($locale);

        $translation = null;

        foreach ($this->translatedAttributes as $attribute) {
            $translation = $this->setAttribute($attribute, null);
        }

        $this->setLocale($appLocale);

        return $translation;
    }

    /**
     * Get an attribute from the model or translation.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatedAttributes)) {
            $original = parent::getAttribute($key);
            return $this->translate() ? $this->translate()->$key : $original;
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model or translation.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {

        if (in_array($key, $this->translatedAttributes)) {

            $translation = $this->translateOrNew($this->getLocale());

            $translation->{$key} = $value ? $value : null;
            # some fix;
            $translation->setTable( $this->getTable().'_translations' );

            $this->cache[$this->getLocale()] = $translation;

            if ($this->getLocale() == $this->getFallback()) parent::setAttribute($key, $value);

            return $translation;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Finish processing on a successful save operation.
     *
     * @param array $options
     *
     * @return void
     */
    protected function finishSave(array $options)
    {   
        $this->translations()->saveMany($this->cache);

        parent::finishSave($options);
    }


    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return app()->getLocale();
    }

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public function setLocale($locale=null)
    {
        $locale = is_null($locale) ? app('config')->get('app.fallback_locale') : $locale;
        return app()->setLocale($locale);
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    protected function getFallback()
    {
        return \Config::get('app.fallback_locale');
    }


    public function getTranslatable()
    {
        return $this->translatedAttributes;
    }


    /**
     * Get the translations relation.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    # abstract public function translations();

    public function translations() {

        $translation = $this->getTranslationInstance();

        return new HasMany($translation->newQuery(), $this, $translation->getTable().'.'.'source_id', $this->getKeyName());

    }


    protected function getTranslationInstance()
    {
        $class = get_class($this);
        $instance = new $class;
        $translation_table = $instance->getTable().'_translations';
        $translation = TranslationFactory::make($translation_table, $class, 'id');
        return $translation;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $translatables = $this->getTranslatable();
        $locale = $this->getLocale();

        foreach ($translatables as $i => $key) {

            if (is_array(parent::getAttribute($key))) {
                $data = parent::getAttribute($key);
                if ( isset($data[$locale]) ) {
                   parent::setAttribute($key,$data[$locale]);
                } else {
                   parent::setAttribute($key,null);
                }

                foreach (app('config')->get('app.locales') as $code => $l) {
                    if ( isset($data[$code]) ) {                        
                        $translation = $this->translateOrNew($code);
                        $translation->{$key} = $data[$code] ? $data[$code] : null;
                        $translation->setTable( $this->getTable().'_translations' );
                        $this->cache[$code] = $translation;
                    }
                }
            }

        }


        return parent::save($options);
    }

}
