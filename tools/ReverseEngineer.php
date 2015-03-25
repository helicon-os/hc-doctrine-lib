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

namespace helicon\doctrine\lib\tools;

/**
 * Description of newPHPClass
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class ReverseEngineer extends AbstractTool
{
    /**
     * Entity manager to use for conversion
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    public $entityManager = null;
    
    /**
     * Keep case of names if possible
     * 
     * @var boolean 
     */
    public $keepCase = true;
    
    public $idFieldSuffixes = array('_id', '_ID', 'Id');
    
    /**
     * Output format to use
     * @var string
     */
    public $outputFormat = null;
    
    /**
     * Destination directory
     * 
     * @var string
     */
    public $outputDirectory = null;
    
    
    /**
     * Normalizes names depending on the keepCase setting
     * 
     * @param type $aName
     * @param type $keepCase
     * @return type
     */
    protected function normalizeName($aName)
    {
        if ($this->keepCase)
        {
          if (strtoupper($aName) == $aName)
          {
              return strtolower($aName);
          }
          else
          {
              return $aName;
          }
        }
    }
    
    /**
     * Strip the Id-suffix from the column name
     * 
     * @param type $columnName
     * @return type
     */
    protected function stripIdFieldSuffix($columnName)
    {
        foreach ($this->idFieldSuffixes as $s)
        {
            $columnName = preg_replace('/'.$s.'$/', '', $columnName);
            if ($columnName !== $columnName)
                return $columnName; // column name changed ===> RETURN
        }
        return $columnName;
    }
    /**
     * Generates a field column name
     * 
     * @param type $columnName
     * @param type $fk
     * @return type
     */
    private function getFieldNameForColumn($columnName, $fk = false)
    {
        $columnName = $this->normalizeName($columnName);

        // Replace _id if it is a foreignkey column
        if ($fk) {
            $columnName = $this->stripIdFieldSuffix($columnName);
        }
        return \Doctrine\Common\Inflector\Inflector::camelize($columnName);
    }
    
    /**
     * 
     * @param \Doctrine\ORM\EntityManager $em
     * @return $metadata \Doctrine\ORM\Mapping\ClassMetadata[];
     */
    protected function loadMetadataFromDb(\Doctrine\ORM\EntityManager $em, $keepCase = true)
    {
        $sm = $em->getConnection()->getSchemaManager();
        $driver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver($sm);
        $em->getConfiguration()->setMetadataDriverImpl($driver);
        $cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory($em);
        $cmf->setEntityManager($em);
        
        //
        // Generate nice names
        //
        

        foreach ($sm->listTableNames() as $tableName) {
            $driver->setClassNameForTable($tableName, \Doctrine\Common\Inflector\Inflector::classify($this->normalizeName($tableName)));
            $td = $sm->listTableDetails($tableName);
            
            // generate standard column names
            
            foreach ($td->getColumns() as $cd) /* @var \Doctrine\DBAL\Schema\Column $cd */
            {
                $driver->setFieldNameForColumn($tableName, 
                        $cd->getName(), 
                        $this->getFieldNameForColumn($cd->getName(), false));
            }
            
            // For Foreign keys special names are generated
            
            foreach ($td->getForeignKeys() as $fd) /* @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $fd */
            {
                foreach ($fd->getLocalColumns() as $columnName)
                {
                  $driver->setFieldNameForColumn($tableName, 
                          $columnName, 
                          $this->getFieldNameForColumn($columnName, true));
                }
            }
            
            
        }
        $classes = $driver->getAllClassNames();
        return $cmf->getAllMetadata(); /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata[] */
    }

    /**
     * @return $metadata \Doctrine\ORM\Mapping\ClassMetadata[];
     */
    public function getMetadata()
    {
        return $this->loadMetadataFromDb($this->entityManager);
    }
    
}
