<?php

/*
 * Copyright (c) 2015, Andreas Prucha, Helicon Software Development
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace helicon\doctrine\lib\types;

/**
 * UTC-Timestamp Field
 * 
 * Stores and reads timestamps as UTC
 */
class UtcDateTimeType extends \Doctrine\DBAL\Types\DateTimeType
{
    static private $utc = null;

    public function convertToDatabaseValue($value, Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $utcDateTime = clone ($value);
        $utcDateTime->setTimezone((self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC')));
        
        return $utcDateTime->format('Y-m-d H:i:s').'.'.round($utcDateTime->format('u')/100);
      
    }

    public function convertToPHPValue($value, Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
      
        if ($value === null || $value === '') {
            return null;
        }

        $val = new \DateTime ($value,
            (self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC'))
        );
        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
        return $val;
    }
}
