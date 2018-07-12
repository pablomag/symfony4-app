<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="E-mail already registered")
 * @UniqueEntity(fields="username", message="Username already exists")
 */
class User implements UserInterface, \Serializable
{
	const ROLE_USER = 'ROLE_USER';
	const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
	* @ORM\Column(type="string", length=50, unique=true)
	* @Assert\NotBlank()
	* @Assert\Length(min=3, max=50)
	*/
    private $username;
	/**
	 * @Assert\NotBlank()
	 * @Assert\Length(min=3, max=50)
	 */
	private $plainPassword;

	/**
	 * @ORM\Column(type="string", length=254)
	 */
    private $password;

	/**
	 * @ORM\Column(type="string", length=254, unique=true)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 */
    private $email;

	/**
	 * @ORM\Column(type="string", length=254)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=5, max=254)
	 */
    private $fullname;
	/**
	 * @var array
	 * @ORM\Column(type="simple_array")
	 */
    private $roles;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\MicroPost", mappedBy="user")
	 */
	private $posts;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="following")
	 */
	private $followers;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followers")
	 * @ORM\JoinTable(name="following",
	 *		joinColumns={
	 *			@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *		inverseJoinColumns={
	 *     		@ORM\JoinColumn(name="following_user_id", referencedColumnName="id")})
	 */
	private $following;
	/**
	 * @ORM\Column(type="string", nullable=true, length=30)
	 */
	private $confirmationToken;
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\MicroPost", mappedBy="likedBy")
	 */
	private $postsLiked;

	public function __construct()
	{
		$this->posts = new ArrayCollection();
		$this->followers = new ArrayCollection();
		$this->following = new ArrayCollection();
		$this->postsLiked = new ArrayCollection();
		$this->roles = [self::ROLE_USER];
		$this->active = false;
	}

	/**
	 * @return Collection|User[]
	 */
	public function getFollowers(): Collection
	{
		return $this->followers;
	}

	/**
	 * @return Collection|User[]
	 */
	public function getFollowing(): Collection
	{
		return $this->following;
	}

	/**
	 * @return Collection|MicroPost[]
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

	public function getPlainPassword(): ?string
	{
		return $this->plainPassword;
	}

	public function setPlainPassword(string $plainPassword): self
	{
		$this->plainPassword = $plainPassword;

		return $this;
	}

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getRoles()
	{
		return $this->roles;
	}

	public function setRoles(array $roles)
	{
		return $this->roles = $roles;
	}

	public function follow(User $userToFollow)
	{
		if ($this->getFollowing()->contains($userToFollow))
		{
			return;
		}

		$this->getFollowing()->add($userToFollow);
	}

	/**
	 * @return Collection
	 */
	public function getPostsLiked()
	{
		return $this->postsLiked;
	}

	public function serialize()
	{
		return serialize(
		[
			$this->id,
			$this->username,
			$this->password,
			$this->active
		]);
	}

	public function unserialize($serialized)
	{
		list(
			$this->id,
			$this->username,
			$this->password,
			$this->active
			) = unserialize($serialized);
	}

	public function getSalt()
	{
		return null;
	}

	public function eraseCredentials()
	{}

	/**
	 * @return mixed
	 */
	public function getConfirmationToken()
	{
		return $this->confirmationToken;
	}

	/**
	 * @param mixed $confirmationToken
	 */
	public function setConfirmationToken($confirmationToken): void
	{
		$this->confirmationToken = $confirmationToken;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 */
	public function setActive($active): void
	{
		$this->active = $active;
	}

	public function isActive()
	{
		return $this->active;
	}
}
