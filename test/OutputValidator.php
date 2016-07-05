<?php

class OutputValidator
{
    private $requiredFields;
    private $optionalFields;
    private $arrayFields;

    public function __construct($requiredFields, $optionalFields = [], $arrayFields = [])
    {
        $this->requiredFields = $requiredFields;
        $this->optionalFields = $optionalFields;
        $this->arrayFields = $arrayFields;
    }

    /**
     * Checks if a value has the expectedType or not
     * @param string $key
     * @param mixed $value
     * @param string $expectedType
     * @return bool
     * @throws ValidatorException
     */
    private function isValidValue ($key, &$value, $expectedType)
    {
        switch ($expectedType)
        {
            case 'array':
                switch ($this->arrayFields[$key]["type"])
                {
                    case "value":
                        foreach ($value as $entry) {
                            $this->validateEntry($entry, $this->arrayFields[$key]["fields"]);
                        }
                        return true;
                        break;
                    case "singleValue":
                        foreach ($value as $entry) {
                            if(!gettype($entry) == $expectedType) {
                                return false;
                            }
                        }
                        return true;
                        break;
                    default:
                        return $this->validateEntry($value, $this->arrayFields[$key]["fields"]);
                        break;
                }
            case 'int':
                return is_integer($value);
            case 'bool':
            case 'boolean':
                return is_bool($value);
            case 'float':
                return is_float($value);
            case 'numeric':
                return is_numeric($value);
            case 'date':
                return $this->isValidDate($value);
            case 'url':
                return (strpos($value, "http") !== false);
            case 'imdb':
                return $this->isValidImdbid($value);
                break;
            case 'basic-media':
                return $this->validateEntry($value, ["required" => [
                    "idm" => "int",
                    "mediaType" => "int",
                    "mediaStyle" => "string",
                    "imdb" => "string",
                    "rating" => "numeric",
                    "name" => "string",
                    "original_name" => "string",
                    "year" => "int",
                    "plot" => "string",
                    "id_media" => "string",
                ]]);
            case 'string':
                if(!gettype($value) == $expectedType) {
                    return false;
                }

                $expectedType = trim(strip_tags($expectedType));

                return (!empty($expectedType));
                break;
            default:
                return (gettype($value) == $expectedType);
        }
    }

    /**
     * Verifies that the object begin sent to kraken has the required fields
     *
     * @param array $item
     *
     * @return bool
     * @throws ValidatorException if integrity check failed
     */
    public function checkIntegrity ($item)
    {
        foreach ($this->requiredFields as $field => $type)
        {
            if (!isset($item[$field]) || empty($item[$field]) || !$this->isValidValue($field, $item[$field], $type)) {
                throw new ValidatorException(ValidatorException::INVALID_INTEGRITY, $field . ':' . var_dump($item));
            }
        }

        foreach ($this->optionalFields as $field => $expectedType)
        {
            if (!isset($item[$field]) || ($expectedType != 'boolean' && empty($item[$field]))) {
                continue;
            }
            if (!$this->isValidValue($field, $item[$field], $expectedType)) {
                throw new ValidatorException(ValidatorException::INVALID_INTEGRITY, "$field: " . var_dump($item));
            }
        }
        return true;
    }

    /**
     * Validates an array
     * @param array $entry
     * @param array $fieldList
     * @return bool
     * @throws ValidatorException
     */
    private function validateEntry ($entry, $fieldList)
    {
        foreach ($fieldList as $type => $fields)
        {
            foreach ($fields as $field => $expectedType)
            {
                if (!isset($entry[$field]) || (!in_array($expectedType, ['boolean', "int", "integer"]) && empty($entry[$field])) || ($expectedType == "integer" && !is_numeric($entry[$field]))) {
                    if ($type == "optional") {
                        continue;
                    } else {
                        throw new ValidatorException(ValidatorException::INVALID_INTEGRITY, "$field: " . var_dump($entry));
                    }
                }
                if (!$this->isValidValue($field, $entry[$field], $expectedType)) {
                    throw new ValidatorException(ValidatorException::INVALID_INTEGRITY, "$field: " . var_dump($entry));
                }
            }
        }
        return true;
    }

    /**
     * Checks if an string is a valid imdbid
     *
     * @param string $string
     *
     * @return bool
     ***/
    private function isValidImdbid ($string) {
        preg_match("/tt([0-9]+)/", $string, $matches);
        if (sizeof($matches) == 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks if given date is valid
     * @param string $date
     * @return bool
     */
    private function isValidDate ($date)
    {
        try {
            new \DateTime($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
class ValidatorException extends \Exception {

    const INVALID_INTEGRITY = "invalid object integrity";
    const CRAWLING_ERROR = "provider couldn't be crawled properly";

    public function __construct($constant, $extraInfo, $code = 0, \Exception $previous = null) {

        parent::__construct($constant . ': ' . $extraInfo, $code, $previous);
    }
}