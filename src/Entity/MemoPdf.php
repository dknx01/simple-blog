<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 13.08.20
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class MemoPdf
{
    /**
     * @ORM\Column(type="string")
     */
    private $filename;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return MemoPdf
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     * @return MemoPdf
     */
    public function setFilename($filename): MemoPdf
    {
        $this->filename = $filename;
        return $this;
    }

}