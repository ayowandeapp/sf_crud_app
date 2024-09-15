<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PostDTO
{

    #[Assert\NotBlank(groups: ["create"])]
    #[Assert\Length(min: 5)]
    private ?string $title = null;

    #[Assert\NotBlank(groups: ["create"])]
    #[Assert\Length(min: 10)]
    private ?string $content = null;

    /**
     * Get the value of title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public function setTitle($title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent($content): static
    {
        $this->content = $content;

        return $this;
    }
}
