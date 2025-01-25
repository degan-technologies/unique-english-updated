<?php

namespace App\Services;

use App\Helper\Lang\Error\ErrorLangAmharic;
use App\Helper\Lang\Error\ErrorLangEnglish;
use Illuminate\Http\Request;

class LangService {

    protected $request;

    public function __construct(Request $request) {
        $this->request = $request->header('Accept-Language');
    }

    /**
     * Retrieve the language array based on the given key.
     *
     * @param string $key this key is translation key example ['name' => 'Name"]
     * @return mixed
     */
    public function getLang(string $key): mixed {

        $lang = $this->request;
        
        switch ($lang) {
            case AMAHARIC:
                return ErrorLangAmharic::get($key);
            case ENGLISH:
                return ErrorLangEnglish::get($key);
            default:
                return ErrorLangEnglish::get($key);
        }
    }
}