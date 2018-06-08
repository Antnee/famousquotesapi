<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Quote
 *
 * @ORM\Table(name="quote")
 * @ORM\Entity
 */
class Quote implements JsonSerializable
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
     * @ORM\Column(name="text", type="text", length=0, nullable=false)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="author_id", type="string", length=36, nullable=false)
     */
    private $authorId;

    /**
     * @var Author
     */
    private $author;

    private function __construct(string $id, string $text, string $authorId=null, Author $author=null)
    {
        $this->id = $id;
        $this->text = $text;
        $this->author = $author;
        $this->authorId = $this->author ? $author->getId() : $authorId;
    }

    public static function newQuoteWithAuthorId(string $id, string $text, string $authorId): Quote
    {
        return new self($id, $text, $authorId);
    }

    public static function newQuoteWithAuthor(string $id, string $text, Author $author): Quote
    {
        return new self($id, $text, null, $author);
    }

    /**
     * Get Quote ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get Quote Text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get Quote Author
     *
     * @return Author|null
     */
    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    /**
     * Get a Clone with Updated Author
     *
     * @param Author $author
     * @return Quote
     */
    public function withAuthor(Author $author): self
    {
        $clone = clone $this;
        $clone->author = $author;
        $clone->authorId = $author->getId();
        return $clone;
    }

    /**
     * Get a Clone with Updated Text
     *
     * @param string $text
     * @return Quote
     */
    public function withText(string $text): self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'text' => $this->getText(),
            'author' => $this->getAuthor(),
        ];
    }
}
