<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use mysql_xdevapi\Exception;


/**
 * @ORM\Entity(repositoryClass="App\Repository\DataSetTypeRepository")
 */
class DataSetType
{
    const TYPE_DAILY = 1;
    const TYPE_WEEKLY = 2;
    const TYPE_MONTHLY = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;


    /**
     * @OneToMany(targetEntity="DataSet", mappedBy="DataSetType")
     */
    private $DataSets;


    public function __construct()
    {
        $this->DataSets = new ArrayCollection();
        $this->FileTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|DataSet[]
     */
    public function getDataSets(): Collection
    {
        return $this->DataSets;
    }

    public function addDataSet(DataSet $dataSet): self
    {
        if (!$this->DataSets->contains($dataSet)) {
            $this->DataSets[] = $dataSet;
            $dataSet->setDataSetType($this);
        }

        return $this;
    }

    public function removeDataSet(DataSet $dataSet): self
    {
        if ($this->DataSets->contains($dataSet)) {
            $this->DataSets->removeElement($dataSet);
            // set the owning side to null (unless already changed)
            if ($dataSet->getDataSetType() === $this) {
                $dataSet->setDataSetType(null);
            }
        }

        return $this;
    }



    public function getPeriodStartDefault()
    {
        switch ($this->getId()){
            case self::TYPE_DAILY:
                return (new \DateTime());
                break;
            case self::TYPE_WEEKLY:
                return new \DateTime('first day of this week');
                break;
            case self::TYPE_MONTHLY:
                return new \DateTime('first day of this month');
            default:
                throw new \Exception('Period Default Start called for  undefined DataSetType');
        }
    }


    public function getPeriodEndDefault()
    {
        switch ($this->getId()){
            case self::TYPE_DAILY:
                return (new \DateTime());
                break;
            case self::TYPE_WEEKLY:
                return new \DateTime('last day of this week');
                break;
            case self::TYPE_MONTHLY:
                return new \DateTime('last day of this month');
            default:
                throw new \Exception('Period Default Start called for  undefined DataSetType');
        }
    }
}
