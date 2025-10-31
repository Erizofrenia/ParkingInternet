// Ejecutar carga del perfil al inicio
document.addEventListener('DOMContentLoaded', function() {
    loadRates();
    loadRecords();
    loadUserProfile(); // <<--- se agrega aquí
});

// ============================
// TARIFAS
// ============================
function loadRates(){
    fetch('controladores/tarifas.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=list'
    })
    .then(r => r.json())
    .then(data => {
        let tbody = document.getElementById('ratesTable');
        tbody.innerHTML='';
        data.forEach(rate=>{
            tbody.innerHTML+=`
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <td class="p-3">${rate.tipo_vehiculo}</td>
                    <td class="p-3">$${rate.precio_hora}</td>
                    <td class="p-3">$${rate.precio_minimo}</td>
                    <td class="p-3">
                        <button class="text-primary-500 hover:text-primary-600 mr-2" onclick="editRate(${rate.id}, '${rate.tipo_vehiculo}', ${rate.precio_hora}, ${rate.precio_minimo})">Editar</button>
                        <button class="text-red-500 hover:text-red-600" onclick="deleteRate(${rate.id})">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    });
}

function addRate(){
    let tipo = document.getElementById('rateVehicleType').value;
    let hora = document.getElementById('rateHourlyPrice').value;
    let min = document.getElementById('rateMinPrice').value;

    fetch('controladores/tarifas.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=add&tipo_vehiculo=${tipo}&precio_hora=${hora}&precio_minimo=${min}`
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            closeModal('rateModal');
            loadRates();
        } else alert(data.error);
    });
}

function editRate(id, tipo, hora, min){
    let newTipo = prompt('Tipo de vehículo', tipo);
    if(!newTipo) return;
    let newHora = prompt('Precio por hora', hora);
    if(!newHora) return;
    let newMin = prompt('Precio mínimo', min);
    if(!newMin) return;

    fetch('controladores/tarifas.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=update&id=${id}&tipo_vehiculo=${newTipo}&precio_hora=${newHora}&precio_minimo=${newMin}`
    }).then(r=>r.json()).then(data=>{
        if(data.success) loadRates();
    });
}

function deleteRate(id){
    if(!confirm('¿Eliminar esta tarifa?')) return;
    fetch('controladores/tarifas.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=delete&id=${id}`
    }).then(r=>r.json()).then(data=>{
        if(data.success) loadRates();
    });
}

// ============================
// REGISTROS DE VEHÍCULOS
// ============================
function loadRecords(){
    fetch('controladores/registros.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=list'
    })
    .then(r => r.json())
    .then(data => {
        let tbody = document.getElementById('recordsTable');
        tbody.innerHTML='';
        data.forEach(rec=>{
            tbody.innerHTML+=`
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <td class="p-3">${rec.placa}</td>
                    <td class="p-3">${rec.modelo_color}</td>
                    <td class="p-3">${rec.tipo_vehiculo}</td>
                    <td class="p-3">${rec.cliente_nombre}</td>
                    <td class="p-3">${rec.entrada}</td>
                    <td class="p-3">${rec.salida ?? '-'}</td>
                    <td class="p-3">${rec.total ?? '-'}</td>
                    <td class="p-3">
                        ${!rec.cobrado ? `<button onclick="startExit('${rec.barcode}')" class="text-green-500 hover:text-green-700">Salida</button>` : 'Cobrado'}
                    </td>
                </tr>
            `;
        });
    });
}

// ============================
// REGISTRAR ENTRADA
// ============================
function registerEntry(){
    let tipo = document.getElementById('vehicleType').value;
    let placa = document.getElementById('licensePlate').value;
    let modelo = document.getElementById('modelColor').value;
    let cliente = document.getElementById('customerName').value;

    fetch('controladores/registros.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=registerEntry&tipo_vehiculo=${tipo}&placa=${placa}&modelo_color=${modelo}&cliente_nombre=${cliente}`
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            alert('Vehículo registrado!');
            closeModal('parkModal');
            loadRecords();
        } else alert(data.error);
    });
}

// ============================
// PROCESAR SALIDA
// ============================
function startExit(barcode){
    fetch('controladores/registros.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=processExit&barcode=${barcode}`
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            openModal('exitModal');
            document.getElementById('exitPlate').textContent = data.placa;
            document.getElementById('exitModel').textContent = data.modelo;
            document.getElementById('exitEntry').textContent = data.entrada;
            document.getElementById('totalAmount').textContent = `$${data.total}`;
            document.getElementById('barcodeInput').value = barcode;
        } else alert(data.error);
    });
}

function completeExit(){
    let barcode = document.getElementById('barcodeInput').value;
    let received = document.getElementById('receivedAmount').value;

    fetch('controladores/registros.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=completeExit&barcode=${barcode}&received=${received}`
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            alert('Salida completada!');
            closeModal('exitModal');
            loadRecords();
        } else alert(data.error);
    });
}

// ============================
// MODALES
// ============================
function openModal(id){ document.getElementById(id).classList.remove('hidden'); }
function closeModal(id){ document.getElementById(id).classList.add('hidden'); }

// ============================
// PERFIL DE USUARIO
// ============================
// Cargar perfil al iniciar
function loadUserProfile() {
    fetch('controladores/get_user.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=getProfile'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            document.getElementById('profileName').value = user.nombre || '';
            document.getElementById('profileEmail').value = user.email || '';
            document.getElementById('profilePhone').value = user.telefono || '';
            document.getElementById('profileRole').value = user.puesto || '';

            if (user.perfil_img) {
                document.getElementById('profileImage').src = user.perfil_img;
                document.getElementById('profileImage').classList.remove('hidden');
                document.getElementById('profileIcon').classList.add('hidden');
            }
        } else console.error('Error al cargar perfil:', data.error);
    })
    .catch(err => console.error('Error de conexión:', err));
}

// Cambiar imagen de perfil temporalmente
document.getElementById('profileImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImage').src = e.target.result;
            document.getElementById('profileImage').classList.remove('hidden');
            document.getElementById('profileIcon').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// Guardar perfil
document.getElementById('saveProfileBtn').addEventListener('click', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('nombre', document.getElementById('profileName').value);
    formData.append('email', document.getElementById('profileEmail').value);
    formData.append('telefono', document.getElementById('profilePhone').value);
    formData.append('puesto', document.getElementById('profileRole').value);

    const imageInput = document.getElementById('profileImageInput');
    if(imageInput.files[0]) formData.append('perfil_img', imageInput.files[0]);

    fetch('controladores/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Perfil actualizado correctamente');
            loadUserProfile();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(err => alert('Error de conexión: ' + err));
});
