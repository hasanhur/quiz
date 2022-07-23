<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass=TestRepository::class)
 */
class Test
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Subject::class, inversedBy="tests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subject;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, unique=true, options={"collation":"utf8_general_ci"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="test", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity=UserTest::class, mappedBy="test", orphanRemoval=true)
     */
    private $userTests;

    /**
     * @ORM\Column(type="integer")
     */
    private $max_time;

    /**
     * @ORM\Column(type="datetime")
     */
    private $active_from;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = true;

    public function __construct()
    {
        if (empty($this->created_at)) {
            $this->created_at = new \DateTime();
        }
        $this->updated_at = new \DateTime();
        $this->max_time = \DateTime::createFromFormat('H:i:s', "00:00:00");
        $this->active_from = \DateTime::createFromFormat('', "");
        $this->userTests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestion(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->question->contains($question)) {
            $this->question[] = $question;
            $question->setTest($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->question->contains($question)) {
            $this->question->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getTest() === $this) {
                $question->setTest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserTest[]
     */
    public function getUserTests(): Collection
    {
        return $this->userTests;
    }

    public function addUserTest(UserTest $userTest): self
    {
        if (!$this->userTests->contains($userTest)) {
            $this->userTests[] = $userTest;
            $userTest->setTestId($this);
        }

        return $this;
    }

    public function removeUserTest(UserTest $userTest): self
    {
        if ($this->userTests->contains($userTest)) {
            $this->userTests->removeElement($userTest);
            // set the owning side to null (unless already changed)
            if ($userTest->getTestId() === $this) {
                $userTest->setTestId(null);
            }
        }

        return $this;
    }

    public function getMaxTime(): ?int
    {
        return $this->max_time;
    }

    public function setMaxTime(array $max_time): self
    {
        $this->max_time = (int)$max_time['hour'] * 3600 + (int)$max_time['minute'] * 60 + (int)$max_time['second'];

        return $this;
    }

    public function getActiveFrom(): ?\DateTimeInterface
    {
        return $this->active_from;
    }

    public function setActiveFrom(\DateTimeInterface $active_from): self
    {
        $this->active_from = $active_from;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }
}
