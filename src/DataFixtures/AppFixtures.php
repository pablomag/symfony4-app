<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
	private const FIRSTNAMES =
	[
		"Mary", "John", "Albert", "Sean", "Paul", "Rico", "Gwen", "Hayley", "Peter", "Lights",
		"Bert", "Marie", "Milla", "Steve", "Robert", "Anne", "Mike", "Shirley", "Lindsey",
		"Rebecca", "Megan", "Pablo", "Lauren", "Joe", "Tom", "Penelope", "Sharon", "Ian", "Todd",
		"Bob", "Ben", "Charly", "Annabelle", "Zoe", "Yuri", "Sid", "Moe", "Norman", "Bill", "Rud",
		"Aaron", "Avril", "Evangeline", "Evan", "Leo", "Alicia", "Martha", "Artyom", "Corvo", "Mario"
	];

	private const LASTNAMES =
	[
		"Williams", "Poxleitner", "Plott", "Day", "Suave", "North", "Bush", "Stefani", "Gold",
		"Crystal", "Roberts", "Marie", "Johnson", "Hardy", "Norton", "Lucas", "Turner", "Gee",
		"Caldwell", "Rice", "Howard", "Campbell", "Owen", "Beckham", "Mint", "Miller", "Trump",
		"Yeomi", "Atlas", "Bell", "Bird", "Altmer", "Bates", "Rodenthal", "Reid", "Salt", "King",
		"Fernandez", "Lavigne", "Sinclair", "Everett", "Diaz", "Seth", "Jolie", "Voight", "Pitt"
	];

	private const DOMAINS =
	[
		"paramore.com", "day9.tv", "lights.ca", "invisible.co", "app.com", "nodoubt.com",
		"fake.net", "hack.net", "sundry.com", "interplay.com", "valve.com", "steam.com",
		"app.com", "google.com", "geogle.com", "yahoo.com", "hotmail.com", "yandex.ru"
	];

	private const WORDS =
	[
		"dancing", "street", "now", "yesterday", "on", "I", "heart", "offer", "mine",
		"hello", "penis", "in", "a", "tree", "donkey", "mouse", "jumps", "milk",
		"noted", "runs", "squirted", "all", "over", "the", "place", "randomly", "yours",
		"generated", "text", "is", "so", "much", "fun", "don't", "you", "think", "fine",
		"I", "mean", "the", "possibilities", "are", "endless", "funny", "stupid", "all",
		"hilarious", "detach", "my", "claws", "nowhere", "to", "be", "found", "us",
		"right", "wrong", "good", "evil", "record", "code", "mad", "crazy", "sure",
		"piece", "of", "shit", "never", "day", "say", "stop", "die", "when", "shower",
		"mistery", "last", "of", "that", "then", "you", "will", "was", "lifted", "door",
		"asking", "no", "questions", "angels", "miracle", "not", "yes", "ball", "castle",
		"then", "more", "fuck", "words", "interview", "job", "over", "that", "cunt",
		"window", "a", "the", "but", "live", "tell", "signature", "pen", "cat", "in",
		"wished", "stars", "none", "stranger", "things", "below", "believe", "never",
		"know", "I", "you", "him", "look", "away", "way", "stop", "nothing", "owl",
		"music", "in", "the", "air", "a", "hat", "never", "usually", "flops", "fart"
	];

	private const ENDINGS = [".", "?", ".", " XD", ".", ".", " :(", "!", "?!", ".", ".", " :D", "."];

	private $generatedUsers;

	private function generatePost()
	{
		$postLength = rand(5, 25);

		$post = "";

		for ($i = 0; $i < $postLength; $i++)
		{
			$w = rand(0, count(self::WORDS)-1);

			$word = self::WORDS[$w];

			if ($i == 0)
			{
				$word = strtoupper(substr($word, 0, 1)).substr($word, 1, strlen($word));
			} else { $post .= " "; }

			$post .= $word;
		}

		$e = self::ENDINGS[rand(0, count(self::ENDINGS)-1)];
		$post .= $e;

		return $post;
	}

	private function generateUser()
	{
		$username = self::FIRSTNAMES[rand(0, count(self::FIRSTNAMES)-1)];
		$fullname = $username." ".self::LASTNAMES[rand(0, count(self::LASTNAMES)-1)];

		$username = strtolower($username).rand(1, 99);

		$email = $username."@".self::DOMAINS[rand(0, count(self::DOMAINS)-1)];

		$randomUser =
		[
			'username' => $username,
			'email' => $email,
			'password' => 'user',
			'fullname' => $fullname,
			'roles' => [User::ROLE_USER]
		];

		return $randomUser;
	}

	/**
	 * @var UserPasswordEncoderInterface
	 */
	private $passwordEncoder;

	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

	public function load(ObjectManager $manager)
	{
		$this->loadUsers($manager);
		$this->loadMicroPosts($manager);
	}

	public function loadMicroPosts(ObjectManager $manager)
	{
		$numberOfPosts = 120;

		for ($i = 0; $i < $numberOfPosts; $i++)
		{
			$p = $this->generatePost();
			$d = new \DateTime();
			$d->modify('-'.rand(0, 120).' day');

			$microPost = new MicroPost();
			$microPost->setText($p);
			$microPost->setTime($d);

			$ref = rand(0, count($this->generatedUsers)-1);

			$microPost->setUser($this->getReference(
				$this->generatedUsers[$ref]
			));

			$manager->persist($microPost);
		}

		$manager->flush();
	}

	public function loadUsers(ObjectManager $manager)
	{
		$numberOfUsers = 40;

		for ($i = 0; $i < $numberOfUsers; $i++)
		{
			$u = $this->generateUser();

			$user = new User();
			$user->setUsername($u['username']);
			$user->setFullname($u['fullname']);
			$user->setEmail($u['email']);
			$user->setPassword(
				$this->passwordEncoder->encodePassword($user, $u['password']));
			$user->setRoles($u['roles']);
			$user->setActive(true);

			$this->addReference($u['username'], $user);

			$this->generatedUsers[] = $u['username'];

			$manager->persist($user);
			$manager->flush();
		}
	}
}
