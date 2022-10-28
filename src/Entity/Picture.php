<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
/**
 * @Uploadable()
 */
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['GetPicture'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['GetPicture'])]
    private ?string $realName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['GetPicture'])]
    private ?string $realPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['GetPicture'])]
    private ?string $publicPath = null;
    
    #[ORM\Column]
    private ?bool $status = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['GetPicture'])]
    private ?string $mimeType = null;


    /**
     *
     * @var File|null
     * @UploadableField(mapping="images",fileNameProperty="realPath")
     */
    private $file;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['GetPicture'])]
    private ?\DateTimeInterface $uploadDate = null;

    #[ORM\ManyToOne(inversedBy: 'image')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): self
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getUploadDate(): ?\DateTimeInterface
    {
        return $this->uploadDate;
    }

    public function setUploadDate(\DateTimeInterface $uploadDate): self
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }
}
