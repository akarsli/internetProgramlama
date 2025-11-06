using System.ComponentModel.DataAnnotations;

namespace OgrenciYonetimProjesi.Models
{
    public class Ogrenci
    {
        public int Id { get; set; } 

        [Required(ErrorMessage = "Ad alanı zorunludur.")]
        [StringLength(50)]
        public string? Ad { get; set; }

        [Required(ErrorMessage = "Soyad alanı zorunludur.")]
        [StringLength(50)]
        public string? Soyad { get; set; }
        [Display(Name = "Öğrenci Numarası")]
        [Required(ErrorMessage = "Öğrenci Numarası zorunludur.")]
        public int OgrenciNo { get; set; }
    }
}