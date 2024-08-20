<?php

namespace Molbal\AiPhpdoc;

use Exception;

class FileParser
{
    /**
     * Extracts a list of functions from a string containing PHP code.
     *
     * @param string $code The string of PHP code.
     * @return array An array of functions, each of which is an associative array with the following keys:
     *   - name: the name of the function
     * @throws Exception if the file does not exist
     */
    public function getFunctionsFromString(string $code): array
    {
        $functions = [];
        $matches = [];

        preg_match_all('/^\s*(\/\*\*.*?\*\/)?\s*(?:(?:public|private|protected)\s+)?(?:static\s+)?function\s+(\w+)\s*\(([^)]*)\)\s*(?::\s?(\S+))?/ms', $code, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $phpdoc = self::getPhpdocFromString($match[0][0]);
            $function = $match[0][0].PHP_EOL.$this->getFunctionBody($code, $match[0][1]);
            $functions[] = [
                'name' => $match[2][0],
                'phpdoc' => $phpdoc,
                'body' => $function,
                'phpdocparams' => $this->getPhpDocParams($phpdoc),
                'functionparams' => $this->getFunctionParams($function)
            ];
        }
        return $functions;
    }
    /**
    * Retrieves the parameters from a given PHPDoc string.
     *
     * @param string|null $phpDoc The PHPDoc string to retrieve parameters from.
     * @return array An array of parameters, each with a type and name.
    */

    private function getPhpDocParams(?string $phpDoc): array
    {
        $params = [];

        if (!$phpDoc) {
            return $params;
        }

        $pattern = '/@param\s+([^\s]+)\s+\$([^\s]+)/';
        if (preg_match_all($pattern, $phpDoc, $matches)) {
            foreach ($matches[1] as $index => $type) {
                $params[] = [
                    'type' => $type,
                    'name' => $matches[2][$index]
                ];
            }
        }
        return $params;
    }
    /**
    * Retrieves the parameters of a given function.
     *
     * @param string $function The function to retrieve parameters from.
     * @return array An array containing the type, name, and default value (if any) of each parameter.
    */
    private function getFunctionParams(string $function): array
    {
        $params = [];

        // Expression régulière pour capturer les paramètres de la fonction
        $pattern = '/function\s+\w+\s*\(([^)]*)\)/';
        if (preg_match($pattern, $function, $matches)) {
            $paramString = $matches[1];

            // Expression régulière pour capturer le type, le nom et la valeur par défaut des paramètres
            $paramPattern = '/(?:(\?\w+|\w+)\s+)?\$(\w+)(?:\s*=\s*([^,]+))?/';
            preg_match_all($paramPattern, $paramString, $paramMatches, PREG_SET_ORDER);

            foreach ($paramMatches as $paramMatch) {
                $params[] = [
                    'type' => isset($paramMatch[1]) ? $paramMatch[1] : null,
                    'name' => $paramMatch[2],
                    'default' => isset($paramMatch[3]) ? trim($paramMatch[3]) : null,
                ];
            }
        }

        return $params;
    }
    /**
    * Checks if the parameters in a function match the parameters in the corresponding PHPDoc block.
     *
     * @param array $functionParams The parameters in the function.
     * @param array $phpDocParams The parameters in the PHPDoc block.
     * @return bool Returns true if the parameters match, false otherwise.
    */
    public static function paramsAreTheSame($functionParams, $phpDocParams)
    {
        if (count($functionParams) !== count($phpDocParams)) {
            return false;
        }

        foreach ($functionParams as $index => $param) {
            if ($param['name'] !== $phpDocParams[$index]['name']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extracts a list of PHPDoc from a string containing PHP code. The function works by using regular expressions.
     *
     * @param string $code The string of PHP code containing a function with.
     * @return ?string null, if the string contains no PHPDoc block, or the PHPDoc comment block, if it contains one:
     **/
    private function getPhpDocFromString(string $code): ?string
    {
        $matches = [];
        preg_match('/^\s*\/\*\*(.*?)\*\//ms', $code, $matches);
        return $matches[0] ?? null;
    }


    /**
     * Extracts a list of functions from a PHP file.
     *
     * @param string $filePath The file path of the PHP file.
     * @return array An array of functions, each of which is an associative array with the following keys:
     *   - name: the name of the function
     *   - hasDocComment: a boolean indicating whether the function has a PHPDoc block or not
     *   - body: the body of the function as a string
     * @throws Exception if the file does not exist
     */
    public function getFunctionsFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        $code = file_get_contents($filePath);
        return self::getFunctionsFromString($code);
    }


    /**
     * Retrieve the body of a function from a given string.
     *
     * @param string $str The string containing the function.
     * @param int $startIndex The index of the string to start searching from.
     *
     * @return string The body of the function.
     */
    private function getFunctionBody(string $str, int $startIndex): string
    {
        $openBraceCount = 0;
        $closeBraceCount = 0;
        $contents = "";

        for ($i=$startIndex; $i<strlen($str); $i++) {
            if ($str[$i] == "{") {
                $openBraceCount++;
            } elseif ($str[$i] == "}") {
                $closeBraceCount++;
            }

            if ($openBraceCount > 0) {
                $contents .= $str[$i];
            }

            if ($openBraceCount > 0 && $openBraceCount == $closeBraceCount) {
                break;
            }
        }

        return $contents;
    }
}
