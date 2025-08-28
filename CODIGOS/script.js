document.getElementById('open_btn').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('open-sidebar');
});

document.querySelectorAll('.side-item').forEach(item => {
    item.addEventListener('click', function () {
        document.querySelectorAll('.side-item').forEach(el => el.classList.remove('active'));
        this.classList.add('active');
    });
});