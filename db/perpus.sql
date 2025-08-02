SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

CREATE TABLE `pengunjung` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `jk` enum('L','P') NOT NULL,
  `pinjam` enum('true','false') NOT NULL DEFAULT 'false',
  `waktu_kunjung` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL,
  `id_pengunjung` int(11) NOT NULL,
  `kode_buku` varchar(100) DEFAULT NULL,
  `jenis_buku` enum('mapel','non-mapel') NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `tgl_pinjam` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_kembali` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('admin','member') NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pass` varchar(225) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `role`, `name`, `email`, `pass`, `created_at`) VALUES
(4, 'admin', 'admin', 'admin@mail.com', 'admin#123#');

ALTER TABLE `pengunjung`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengunjung` (`id_pengunjung`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pengunjung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `pinjaman`
  ADD CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`id_pengunjung`) REFERENCES `pengunjung` (`id`) ON DELETE CASCADE;
COMMIT;