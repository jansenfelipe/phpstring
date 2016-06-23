<?php

namespace JansenFelipe\PHPString\Test;

use JansenFelipe\PHPString\Annotations\Text;
use JansenFelipe\PHPString\Annotations\Date;
use JansenFelipe\PHPString\Annotations\Numeric;

class Event
{
    /**
     * @Text(sequence=1, size=20)
     */
    public $name;

    /**
     * @Date(sequence=2, size=8, format="Ymd")
     */
    public $date;

    /**
     * @Numeric(sequence=3, size=6, decimals=2, decimal_separator="")
     */
    public $price;

    /**
     * @Text(sequence=4, size=100)
     */
    public $description;

}