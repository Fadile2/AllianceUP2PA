<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentRepository")
 * @UniqueEntity(
 *     fields={"email", "matricule"}
 * )
 */
class Adherent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="2", max="255", minMessage="Minimum 2 caractères !", maxMessage="Maximum 255 caractères !")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="2", max="255", minMessage="Minimum 2 caractères !", maxMessage="Maximum 255 caractères !")
     */
    private $firstname;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date(message="Merci de saisir une date correcte et sous le bon format !")
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="L'email '{{ value }}' n'est pas valable. Réessayez !")
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Length(min="7", max="7", minMessage="Le numéro saisi est trop court !", maxMessage="Le numéro saisi est trop long !")
     */
    private $matricule;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\IsTrue(message = "Ce service est accessible uniquement si vous consentez à la politique d'utilisation de vos données personnelles de l'Alliance")
     */
    private $gdpr_consent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Image(mimeTypes={"image/png", "image/jpeg" }, mimeTypesMessage="Ceci n'est pas une image valide !")
     */
    private $picture;

    /**
     * @ORM\Column(type="date")
     */
    private $SubcriptionDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Voucher", mappedBy="adherent")
     */
    private $vouchers;

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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

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

    public function getMatricule(): ?int
    {
        return $this->matricule;
    }

    public function setMatricule(int $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGdprConsent(): ?bool
    {
        return $this->gdpr_consent;
    }

    public function setGdprConsent(bool $gdpr_consent): self
    {
        $this->gdpr_consent = $gdpr_consent;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture($picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getSubcriptionDate(): ?\DateTimeInterface
    {
        return $this->SubcriptionDate;
    }

    public function setSubcriptionDate(\DateTimeInterface $SubcriptionDate): self
    {
        $this->SubcriptionDate = $SubcriptionDate;

        return $this;
    }

    /**
     * @return Collection|Voucher[]
     */
    public function getVouchers(): Collection
    {
        return $this->vouchers;
    }

    public function addVoucher(Voucher $voucher): self
    {
        if (!$this->vouchers->contains($voucher)) {
            $this->vouchers[] = $voucher;
            $voucher->setUser($this);
        }

        return $this;
    }

    public function removeVoucher(Voucher $voucher): self
    {
        if ($this->vouchers->contains($voucher)) {
            $this->vouchers->removeElement($voucher);
            // set the owning side to null (unless already changed)
            if ($voucher->getUser() === $this) {
                $voucher->setUser(null);
            }
        }

        return $this;
    }
}
