<?php

namespace JansenFelipe\PHPString;

use Carbon\Carbon;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;
use JansenFelipe\PHPString\Annotations\Date;
use JansenFelipe\PHPString\Annotations\Layout;
use JansenFelipe\PHPString\Annotations\Numeric;
use JansenFelipe\PHPString\Annotations\Text;
use ReflectionClass;

class PHPString
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     * Constructor.
     */
    public function __construct($class)
    {
        if(!class_exists($class))
            throw new Exception('Class not exits');

        $this->class = $class;

        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Date.php');
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Numeric.php');
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Text.php');

        $this->annotationReader = new AnnotationReader();

        $this->reflectionClass = new ReflectionClass($this->class);
    }

    /**
     * Convert string to object
     *
     * @param $string
     * @return object
     */
    public function toObject($string)
    {
        $object = new $this->class;

        $i = 0;

        foreach($this->reflectionClass->getProperties() as $reflectionProperty)
        {
            foreach($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $propertyAnnotation)
            {
                if ($propertyAnnotation instanceof Layout)
                {
                    $value = substr($string, $i, $propertyAnnotation->size);

                    /*
                     * Date
                     */
                    if ($propertyAnnotation instanceof Date && strlen(trim($value)) > 0)
                        $reflectionProperty->setValue($object, Carbon::createFromFormat($propertyAnnotation->format, $value));

                    /*
                     * Text
                     */
                    if ($propertyAnnotation instanceof Text)
                        $reflectionProperty->setValue($object, trim($value));

                    /*
                     * Numeric
                     */
                    if ($propertyAnnotation instanceof Numeric)
                    {
                        if(!is_numeric($value))
                            throw new Exception("[$value] is not numeric");

                        $formated = (floor($value) == $value)?intval($value):floatval($value);

                        if($propertyAnnotation->decimals > 0 && is_int($formated))
                        {
                            if($propertyAnnotation->decimals >= strlen($value))
                                throw new Exception("Number of decimal places greater than the value [$value]");

                            $formated = floatval(substr($value, 0, strlen($value)-$propertyAnnotation->decimals) .'.'. substr($value, $propertyAnnotation->decimals*-1));
                        }

                        $reflectionProperty->setValue($object, $formated);
                    }



                    //Increment.
                    $i += $propertyAnnotation->size;
                }
            }
        }

        return $object;
    }

    /**
     * Convert object to string
     *
     * @param $object
     * @return string
     */
    public function toString($object)
    {
        if(get_class($object) != $this->class)
            throw new Exception("The object is not an instance of $this->class");

        $string = "";

        foreach($this->reflectionClass->getProperties() as $reflectionProperty)
        {
            foreach($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $propertyAnnotation)
            {
                if ($propertyAnnotation instanceof Layout)
                {
                    $value = $reflectionProperty->getValue($object);

                    if(is_null($value))
                    {
                        $filler = ($propertyAnnotation instanceof Numeric) ? '0' : ' ';
                        $string .= str_pad('', $propertyAnnotation->size, $filler, STR_PAD_RIGHT);
                        break;
                    }

                    /*
                     * Date
                     */
                    if ($propertyAnnotation instanceof Date)
                    {
                        if(!($value instanceof Carbon))
                            throw new Exception("$value is not an instance of Carbon");

                        $string .= $value->format($propertyAnnotation->format);
                    }

                    /*
                     * Text
                     */
                    if ($propertyAnnotation instanceof Text)
                        $string .= str_pad($value, $propertyAnnotation->size, ' ', STR_PAD_RIGHT);

                    /*
                     * Numeric
                     */
                    if ($propertyAnnotation instanceof Numeric)
                    {
                        if (!is_numeric($value))
                            throw new Exception("$value is not numeric");

                        $value = (floor($value) == $value)?intval($value):floatval($value);

                        if($propertyAnnotation->decimals > 0)
                            $value = number_format($value, $propertyAnnotation->decimals, $propertyAnnotation->decimal_separator, '');

                        $string .= str_pad($value, $propertyAnnotation->size, '0', STR_PAD_LEFT);
                    }
                }
            }
        }

        return $string;
    }

    /**
     * Get size layout
     *
     * @return int
     */
    public function getSize()
    {
        $i = 0;

        foreach($this->reflectionClass->getProperties() as $reflectionProperty)
        {
            foreach ($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $propertyAnnotation)
            {
                if ($propertyAnnotation instanceof Layout)
                {
                    $i += $propertyAnnotation->size;
                }
            }
        }

        return $i;
    }
}