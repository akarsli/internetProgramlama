using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using OgrenciYonetimProjesi.Data;
using OgrenciYonetimProjesi.Models;

namespace OgrenciYonetimProjesi.Controllers
{
    public class OgrenciController : Controller
    {
        private readonly UygulamaDbContext _context;

        public OgrenciController(UygulamaDbContext context)
        {
            _context = context;
        }

        // ****************************************************
        // R - Read: Tüm Öğrencileri Listeleme (IActionResult)
        // ****************************************************
        public async Task<IActionResult> Index()
        {
            return View(await _context.Ogrenciler.ToListAsync());
        }

        // ****************************************************
        // C - Create (GET): Formu Göster (IActionResult)
        // ****************************************************
        public IActionResult Ekle()
        {
            return View();
        }

        // ****************************************************
        // C - Create (POST): Veriyi Kaydet (IActionResult)
        // ****************************************************
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Ekle([Bind("Ad,Soyad,OgrenciNo")] Ogrenci ogrenci)
        {
            if (ModelState.IsValid)
            {
                _context.Add(ogrenci);
                await _context.SaveChangesAsync();
                return RedirectToAction(nameof(Index));
            }
            return View(ogrenci);
        }

        // Eksik olan U ve D metotları buraya eklenecektir.

        // ****************************************************
        // U - Update (GET): Düzenleme Formunu Göster (IActionResult)
        // ****************************************************
        public async Task<IActionResult> Duzenle(int? id)
        {
            if (id == null)
            {
                return NotFound(); // ID gelmezse 404 hatası döndür
            }

            // ID'ye göre öğrenciyi veritabanından bul
            var ogrenci = await _context.Ogrenciler.FindAsync(id);

            if (ogrenci == null)
            {
                return NotFound(); // Öğrenci bulunamazsa 404 hatası döndür
            }

            // Bulunan öğrenci verisiyle View'ı göster
            return View(ogrenci);
        }

        // ****************************************************
        // U - Update (POST): Güncel Veriyi Kaydet (IActionResult)
        // ****************************************************
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Duzenle(int id, [Bind("Id,Ad,Soyad,OgrenciNo")] Ogrenci ogrenci)
        {
            // URL'deki ID ile formdan gelen ID uyuşmalı
            if (id != ogrenci.Id)
            {
                return NotFound();
            }

            if (ModelState.IsValid)
            {
                try
                {
                    _context.Update(ogrenci); // Bağlamda güncelleme işaretini koy
                    await _context.SaveChangesAsync(); // Veritabanına kaydet
                }
                catch (DbUpdateConcurrencyException)
                {
                    // Güncelleme sırasında eşzamanlılık (concurrency) hatası olursa
                    if (!_context.Ogrenciler.Any(e => e.Id == ogrenci.Id))
                    {
                        return NotFound();
                    }
                    else
                    {
                        throw;
                    }
                }
                return RedirectToAction(nameof(Index)); // Başarılıysa Listeleme sayfasına yönlendir
            }
            return View(ogrenci); // Hata varsa formu tekrar göster
        }

        // Controllers/OgrenciController.cs (OgrenciController sınıfının içine)

        // ****************************************************
        // D - Delete (GET): Silme Onay Sayfasını Göster (IActionResult)
        // ****************************************************
        public async Task<IActionResult> Sil(int? id)
        {
            if (id == null)
            {
                return NotFound();
            }

            // Öğrenciyi bul
            var ogrenci = await _context.Ogrenciler.FirstOrDefaultAsync(m => m.Id == id);
            
            if (ogrenci == null)
            {
                return NotFound();
            }

            // Onay sayfasını göster
            return View(ogrenci);
        }

        // ****************************************************
        // D - Delete (POST): Silme İşlemini Gerçekleştir (IActionResult)
        // ****************************************************
        [HttpPost, ActionName("Sil")] // URL Sil action'ı POST metodu ile çalışacak
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> SilOnay(int id) // Metot adı çakışmasın diye SilOnay kullandık
        {
            var ogrenci = await _context.Ogrenciler.FindAsync(id);
            
            if (ogrenci != null)
            {
                _context.Ogrenciler.Remove(ogrenci); // Bağlamdan sil
                await _context.SaveChangesAsync(); // Veritabanına uygula
            }
            
            // Silme başarılıysa Listeleme sayfasına yönlendir
            return RedirectToAction(nameof(Index));
        }

    }
}