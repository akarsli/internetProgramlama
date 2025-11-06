using Microsoft.EntityFrameworkCore;
using OgrenciYonetimProjesi.Models;

namespace OgrenciYonetimProjesi.Data
{
    public class UygulamaDbContext : DbContext
    {
        public UygulamaDbContext(DbContextOptions<UygulamaDbContext> options)
            : base(options)
        {
        }

        public DbSet<Ogrenci> Ogrenciler { get; set; } = default!;
    }
}