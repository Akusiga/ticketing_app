// Format Indonesian currency
const formatRupiah = (value) => {
  return 'Rp ' + Number(value).toLocaleString('id-ID');
}

// Initialize tickets object from data attribute
const ticketsData = document.getElementById('ticketsData')?.textContent;
const tickets = ticketsData ? JSON.parse(ticketsData) : {};

const summaryItemsEl = document.getElementById('summaryItems');
const summaryTotalEl = document.getElementById('summaryTotal');
const selectedListEl = document.getElementById('selectedList');
const checkoutButton = document.getElementById('checkoutButton');

function updateSummary() {
  let totalQty = 0;
  let totalPrice = 0;
  let selectedHtml = '';

  Object.values(tickets).forEach(t => {
    const qtyInput = document.getElementById('qty-' + t.id);
    if (!qtyInput) return;
    const qty = Number(qtyInput.value || 0);
    if (qty > 0) {
      totalQty += qty;
      totalPrice += qty * t.price;
      selectedHtml += `<div class="flex justify-between"><span>${t.tipe} x ${qty}</span><span>${formatRupiah(qty * t.price)}</span></div>`;
    }
  });

  summaryItemsEl.textContent = totalQty;
  summaryTotalEl.textContent = formatRupiah(totalPrice);
  selectedListEl.innerHTML = selectedHtml || '<p class="text-gray-500">Belum ada tiket dipilih</p>';
  if (checkoutButton) checkoutButton.disabled = totalQty === 0;
}

// Wire up plus/minus buttons and manual input
document.querySelectorAll('[data-action="inc"]').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const id = e.currentTarget.dataset.id;
    const input = document.getElementById('qty-' + id)
    const info = tickets[id];
    if (!input || !info) return;
    let val = Number(input.value || 0);
    if (val < info.stock) val++;
    input.value = val;
    updateTicketSubtotal(id);
    updateSummary();
  });
});

document.querySelectorAll('[data-action="dec"]').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const id = e.currentTarget.dataset.id;
    const input = document.getElementById('qty-' + id);
    if (!input) return;
    let val = Number(input.value || 0);
    if (val > 0) val--;
    input.value = val;
    updateTicketSubtotal(id);
    updateSummary();
  });
});

document.querySelectorAll('input[id^="qty-"]').forEach(input => {
  input.addEventListener('change', (e) => {
    const el = e.currentTarget;
    const id = el.dataset.id;
    const info = tickets[id];
    let val = Number(el.value || 0);
    if (val < 0) val = 0;
    if (val > info.stock) val = info.stock;
    el.value = val;
    updateTicketSubtotal(id);
    updateSummary();
  });
});

function updateTicketSubtotal(id) {
  const t = tickets[id];
  const qty = Number(document.getElementById('qty-' + id).value || 0);
  const subtotalEl = document.getElementById('subtotal-' + id);
  if (subtotalEl) subtotalEl.textContent = formatRupiah(qty * t.price);
}

// Checkout modal
window.openCheckout = function () {
  const modal = document.getElementById('checkout_modal');
  const modalItems = document.getElementById('modalItems');
  const modalTotal = document.getElementById('modalTotal');

  let itemsHtml = '';
  let total = 0;
  Object.values(tickets).forEach(t => {
    const qty = Number(document.getElementById('qty-' + t.id).value || 0);
    if (qty > 0) {
      itemsHtml += `<div class="flex justify-between"><span>${t.tipe} x ${qty}</span><span>${formatRupiah(qty * t.price)}</span></div>`;
      total += qty * t.price;
    }
  });

  modalItems.innerHTML = itemsHtml || '<p class="text-gray-500">Belum ada item.</p>';
  modalTotal.textContent = formatRupiah(total);

  if (typeof modal.showModal === 'function') {
    modal.showModal();
  } else {
    modal.classList.add('modal-open');
  }
}

document.getElementById('confirmCheckout').addEventListener('click', async () => {
  const btn = document.getElementById('confirmCheckout');
  btn.setAttribute('disabled', 'disabled');
  btn.textContent = 'Memproses...';

  const items = [];
  Object.values(tickets).forEach(t => {
    const qty = Number(document.getElementById('qty-' + t.id).value || 0);
    if (qty > 0) items.push({ tiket_id: t.id, jumlah: qty });
  });

  if (items.length === 0) {
    alert('Tidak ada tiket dipilih');
    btn.removeAttribute('disabled');
    btn.textContent = 'Konfirmasi';
    return;
  }

  try {
    const eventId = document.getElementById('eventId').value;
    const res = await fetch(document.getElementById('ordersStoreUrl').value, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ event_id: eventId, items })
    });

    if (!res.ok) {
      const text = await res.text();
      throw new Error(text || 'Gagal membuat pesanan');
    }

    const data = await res.json();
    window.location.href = data.redirect || document.getElementById('ordersIndexUrl').value;
  } catch (err) {
    console.log(err);
    alert('Terjadi kesalahan saat memproses pesanan: ' + err.message);
    btn.removeAttribute('disabled');
    btn.textContent = 'Konfirmasi';
  }
});

// Initialize
updateSummary();
