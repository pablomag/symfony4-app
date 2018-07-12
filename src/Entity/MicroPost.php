<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MicroPostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MicroPost
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
	 * @ORM\Column(type="string", length=280)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=10, minMessage="Post too short")
	 */
	private $text;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="postsLiked")
	 * @ORM\JoinTable(name="post_likes",
	 *	 joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
	 *	 inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")})
	 */
	private $likedBy;

	public function __construct()
	{
		$this->likedBy = new ArrayCollection();
	}

	public function getId()
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

	/**
	 * @ORM\PrePersist()
	 */
    public function setTimeOnPersist(): void
	{
		$this->time = new \DateTime();
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

	/**
	 * @return Collection
	 */
	public function getLikedBy()
	{
		return $this->likedBy;
	}

    public function like(User $user)
	{
		if ($this->likedBy->contains($user))
		{
			return;
		}

		$this->likedBy->add($user);
	}
}
