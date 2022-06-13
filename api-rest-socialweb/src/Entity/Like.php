<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Like
 *
 * @ORM\Table(name="likes", indexes={@ORM\Index(name="fk_likes_fotos", columns={"foto_id"}), @ORM\Index(name="fk_likes_users", columns={"user_id"}), @ORM\Index(name="fk_likes_posts", columns={"post_id"})})
 * @ORM\Entity
 */
class Like
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="like_type", type="string", length=255, nullable=true)
     */
    private $likeType;

    /**
     * @var \Foto
     *
     * @ORM\ManyToOne(targetEntity="Foto")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foto_id", referencedColumnName="id")
     * })
     */
    private $foto;

    /**
     * @var \Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     * })
     */
    private $post;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLikeType(): ?string
    {
        return $this->likeType;
    }

    public function setLikeType(?string $likeType): self
    {
        $this->likeType = $likeType;

        return $this;
    }

    public function getFoto(): ?Foto
    {
        return $this->foto;
    }

    public function setFoto(?Foto $foto): self
    {
        $this->foto = $foto;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


}
