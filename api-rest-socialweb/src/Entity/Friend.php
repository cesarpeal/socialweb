<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Friend
 *
 * @ORM\Table(name="friends", indexes={@ORM\Index(name="fk_user2_users", columns={"user_2"}), @ORM\Index(name="fk_user1_users", columns={"user_1"})})
 * @ORM\Entity
 */
class Friend
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
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_1", referencedColumnName="id")
     * })
     */
    private $user1;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_2", referencedColumnName="id")
     * })
     */
    private $user2;

    /**
     * @var int
     *
     * @ORM\Column(name="befriends", type="integer", length=10, nullable=false)
     */
    private $befriends;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): self
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): self
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getBefriends(): ?int
    {
        return $this->befriends;
    }

    public function setBefriends(?int $befriends): self
    {
        $this->befriends = $befriends;

        return $this;
    }


}
