<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataSetRepository")
 */
class DataSet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $period_start;

    /**
     * @ORM\Column(type="date")
     */
    private $period_end;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $upload;


    /**
     * @ManyToOne(targetEntity="DataSetType", inversedBy="DataSets")
     */
    private $Type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPeriodStart(): ?\DateTimeInterface
    {
        return $this->period_start;
    }

    public function setPeriodStart(\DateTimeInterface $period_start): self
    {
        $this->period_start = $period_start;

        return $this;
    }

    public function getPeriodEnd(): ?\DateTimeInterface
    {
        return $this->period_end;
    }

    public function setPeriodEnd(\DateTimeInterface $period_end): self
    {
        $this->period_end = $period_end;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getUpload(): ?bool
    {
        return $this->upload;
    }

    public function setUpload(bool $upload): self
    {
        $this->upload = $upload;

        return $this;
    }

    public function getType(): ?DataSetType
    {
        return $this->Type;
    }

    public function setType(?DataSetType $Type): self
    {
        $this->Type = $Type;

        return $this;
    }


}
