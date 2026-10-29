function fn_ValForm() {
    var sMsg = "";
    var nama = document.getElementById("nama").value;
    var email = document.getElementById("email").value;
    var umur = document.getElementById("umur").value;
    var kategori = document.getElementById("kategori").value;
    var info = "";
    var pilihan = "";
    var polaEmail = /^[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9]@[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9][\.][a-z0-9]{2,4}$/;

    if (nama == "") {
        sMsg += "\n* Nama harus diisi";
    }

    if (email == "") {
        sMsg += "\n* Email harus diisi";
    } else if (!polaEmail.test(email)) {
        sMsg += "\n* Format email tidak valid";
    }

    if (umur == "") {
        sMsg += "\n* Umur harus diisi";
    } else if (isNaN(umur) || umur < 15 || umur > 60) {
        sMsg += "\n* Umur harus berupa angka antara 15 sampai 60";
    }

    if (kategori == "") {
        sMsg += "\n* Jenis acara harus dipilih";
    }

    if (sMsg != "") {
        alert("Peringatan:\n" + sMsg);
        return false;
    } else {
        if (umur <= 25) {
            info = "Peserta Mahasiswa";
        } else {
            info = "Peserta Umum";
        }

        switch (kategori) {
            case "W":
                pilihan = "Workshop";
                break;
            case "S":
                pilihan = "Seminar";
                break;
            case "L":
                pilihan = "Lomba Coding";
                break;
        }

        alert("Terima kasih " + nama + " ! Anda terdaftar di acara " + pilihan + " (" + info + ").");
        return false;
    }
}