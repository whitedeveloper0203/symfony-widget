<?php

// src/AppBundle/Entity/WidgetPlacement.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="widgetplacement")
 */
class WidgetPlacement
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="decimal", scale=1, nullable=true)
     */
    private $density;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $placement;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $url;

    public function getId() {
		  return $this->id;
    }
    
    public function getType() {
		  return $this->type;
    }
    
    public function getDensity() {
		  return $this->density;
    }
    
    public function getEnabled() {
		  return $this->enabled;
    }
    
    public function getPlacement() {
		  return $this->placement;
    }

    public function getUrl() {
		  return $this->url;
    }
    
    public function setType($type) {
      $this->type = $type;
      return $this;
    }
    
    public function setDensity($density) {
      $this->density = $density;
      return $this;
    }
    
    public function setEnabled($enabled) {
      $this->enabled = $enabled;
      return $this;
    }
    
    public function setPlacement($placement) {
      $this->placement = $placement;
      return $this;
    }

    public function setUrl($url) {
      $this->url = $url;
      return $this;
    }
}