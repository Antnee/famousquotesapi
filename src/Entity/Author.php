<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Author
 *
 * @ORM\Table(name="author", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_BDAFD8C85E237E06", columns={"name"})})
 * @ORM\Entity
 */
class Author implements JsonSerializable
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=36, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="quote_count", type="integer", options={"unsigned"=true, "default"=0}, nullable=false)
     */
    private $quoteCount;

    private $quotes;

    public function __construct(string $id, string $name=null, int $quotes=0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->quoteCount = $quotes;
    }

    /**
     * Get Author ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id ;
    }

    /**
     * Get Author Name
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get Count of Quotes by this Author
     *
     * @return int
     */
    public function getQuoteCount(): int
    {
        return $this->quoteCount;
    }

    /**
     * Get Clone With New Name
     *
     * @param string $name
     * @return Author
     */
    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * Get a Clone with Updated Quote Count
     *
     * @param Quote ...$quotes
     * @return Author
     */
    public function withQuotes(Quote ...$quotes): self
    {
        $clone = clone $this;
        $clone->quotes = $quotes;
        $clone->quoteCount = count($quotes);
        return $clone;
    }

    /**
     * @return array|int|mixed
     */
    public function jsonSerialize()
    {
        if ($this->getName()) {
            return [
                'id' => $this->getId(),
                'name' => $this->getName(),
                'quotes' => $this->getQuoteCount(),
            ];
        }

        return $this->getId();
    }

    public function __toString()
    {
        return $this->getId();
    }
}
