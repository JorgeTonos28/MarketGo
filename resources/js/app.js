import './bootstrap';

class ListBuilder {
    constructor(root) {
        this.root = root;
        this.itemsInput = document.getElementById('items-input');
        this.itemsTable = root.querySelector('[data-items-table]');
        this.products = this.parseDataset(root.dataset.products);
        this.supermarkets = this.parseDataset(root.dataset.supermarkets);
        this.sections = this.parseDataset(root.dataset.sections);
        this.supermarketById = new Map(this.supermarkets.map((market) => [market.id, market]));
        this.sectionById = new Map(this.sections.map((section) => [section.id, section]));
        this.items = [];

        this.elements = {
            existingProduct: root.querySelector('[data-existing-product]'),
            existingQuantity: root.querySelector('[data-existing-quantity]'),
            existingUnit: root.querySelector('[data-existing-unit]'),
            existingPrice: root.querySelector('[data-existing-price]'),
            existingSupermarket: root.querySelector('[data-existing-supermarket]'),
            existingSection: root.querySelector('[data-existing-section]'),
            existingSectionName: root.querySelector('[data-existing-section-name]'),
            addExistingButton: root.querySelector('[data-action="add-existing"]'),
            manualName: root.querySelector('[data-manual-name]'),
            manualUnit: root.querySelector('[data-manual-unit]'),
            manualBrand: root.querySelector('[data-manual-brand]'),
            manualCategory: root.querySelector('[data-manual-category]'),
            manualQuantity: root.querySelector('[data-manual-quantity]'),
            manualPrice: root.querySelector('[data-manual-price]'),
            manualSupermarket: root.querySelector('[data-manual-supermarket]'),
            manualSectionName: root.querySelector('[data-manual-section-name]'),
            manualNotes: root.querySelector('[data-manual-notes]'),
            addManualButton: root.querySelector('[data-action="add-manual"]'),
        };

        this.bindEvents();
    }

    parseDataset(value) {
        if (! value) {
            return [];
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            console.error('No se pudo parsear el dataset', error);
            return [];
        }
    }

    bindEvents() {
        const form = this.root.closest('form');

        if (form) {
            form.addEventListener('submit', (event) => {
                if (this.items.length === 0) {
                    event.preventDefault();
                    alert('Agrega al menos un producto a la lista antes de continuar.');
                    return false;
                }

                this.syncInput();

                return true;
            });
        }

        this.elements.addExistingButton?.addEventListener('click', () => this.addExistingItem());
        this.elements.addManualButton?.addEventListener('click', () => this.addManualItem());

        this.elements.existingProduct?.addEventListener('change', () => {
            const productId = Number.parseInt(this.elements.existingProduct.value, 10);
            const product = this.products.find((item) => item.id === productId);

            if (product?.unit && ! this.elements.existingUnit.value) {
                this.elements.existingUnit.value = product.unit;
            }
        });

        this.itemsTable.addEventListener('click', (event) => {
            const target = event.target;

            if (target instanceof HTMLElement && target.dataset.action === 'remove-item') {
                const index = Number.parseInt(target.dataset.index, 10);

                if (! Number.isNaN(index)) {
                    this.items.splice(index, 1);
                    this.render();
                }
            }
        });
    }

    addExistingItem() {
        const productId = Number.parseInt(this.elements.existingProduct?.value ?? '', 10);
        const product = this.products.find((item) => item.id === productId);

        if (! product) {
            alert('Selecciona un producto del catálogo.');
            return;
        }

        const quantity = Number.parseFloat(this.elements.existingQuantity?.value ?? '1') || 1;
        const unit = (this.elements.existingUnit?.value || product.unit || '').trim();
        const estimatedPrice = this.parsePrice(this.elements.existingPrice?.value);
        const supermarketId = this.parseNullableInt(this.elements.existingSupermarket?.value);
        const sectionId = this.parseNullableInt(this.elements.existingSection?.value);
        const sectionNameInput = (this.elements.existingSectionName?.value || '').trim();

        this.items.push({
            type: 'existing',
            product_id: product.id,
            product_name: product.name,
            quantity,
            quantity_unit: unit || product.unit || null,
            estimated_price: estimatedPrice,
            supermarket_id: supermarketId,
            supermarket_name: this.resolveSupermarketName(supermarketId),
            section_id: sectionId,
            section_name: sectionNameInput || this.resolveSectionName(sectionId),
            notes: null,
        });

        this.resetExistingInputs();
        this.render();
    }

    addManualItem() {
        const name = (this.elements.manualName?.value || '').trim();
        const unit = (this.elements.manualUnit?.value || '').trim();

        if (! name || ! unit) {
            alert('Los productos manuales necesitan un nombre y una unidad de medida.');
            return;
        }

        const quantity = Number.parseFloat(this.elements.manualQuantity?.value ?? '1') || 1;
        const estimatedPrice = this.parsePrice(this.elements.manualPrice?.value);
        const supermarketId = this.parseNullableInt(this.elements.manualSupermarket?.value);
        const sectionName = (this.elements.manualSectionName?.value || '').trim();
        const categoryId = this.parseNullableInt(this.elements.manualCategory?.value);

        this.items.push({
            type: 'manual',
            name,
            unit,
            brand: (this.elements.manualBrand?.value || '').trim() || null,
            package_size: null,
            category_id: categoryId,
            quantity,
            quantity_unit: unit,
            estimated_price: estimatedPrice,
            supermarket_id: supermarketId,
            supermarket_name: this.resolveSupermarketName(supermarketId),
            section_id: null,
            section_name: sectionName,
            notes: (this.elements.manualNotes?.value || '').trim() || null,
        });

        this.resetManualInputs();
        this.render();
    }

    parseNullableInt(value) {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const parsed = Number.parseInt(value, 10);

        return Number.isNaN(parsed) ? null : parsed;
    }

    parsePrice(value) {
        if (! value) {
            return null;
        }

        const parsed = Number.parseFloat(value);

        return Number.isNaN(parsed) ? null : parsed;
    }

    resolveSupermarketName(id) {
        if (! id) {
            return 'Por definir';
        }

        return this.supermarketById.get(id)?.name ?? 'Por definir';
    }

    resolveSectionName(id) {
        if (! id) {
            return '';
        }

        return this.sectionById.get(id)?.name ?? '';
    }

    resetExistingInputs() {
        if (this.elements.existingProduct) {
            this.elements.existingProduct.value = '';
        }

        if (this.elements.existingQuantity) {
            this.elements.existingQuantity.value = '1';
        }

        if (this.elements.existingUnit) {
            this.elements.existingUnit.value = '';
        }

        if (this.elements.existingPrice) {
            this.elements.existingPrice.value = '';
        }

        if (this.elements.existingSupermarket) {
            this.elements.existingSupermarket.value = '';
        }

        if (this.elements.existingSection) {
            this.elements.existingSection.value = '';
        }

        if (this.elements.existingSectionName) {
            this.elements.existingSectionName.value = '';
        }
    }

    resetManualInputs() {
        if (this.elements.manualName) {
            this.elements.manualName.value = '';
        }

        if (this.elements.manualUnit) {
            this.elements.manualUnit.value = '';
        }

        if (this.elements.manualBrand) {
            this.elements.manualBrand.value = '';
        }

        if (this.elements.manualCategory) {
            this.elements.manualCategory.value = '';
        }

        if (this.elements.manualQuantity) {
            this.elements.manualQuantity.value = '1';
        }

        if (this.elements.manualPrice) {
            this.elements.manualPrice.value = '';
        }

        if (this.elements.manualSupermarket) {
            this.elements.manualSupermarket.value = '';
        }

        if (this.elements.manualSectionName) {
            this.elements.manualSectionName.value = '';
        }

        if (this.elements.manualNotes) {
            this.elements.manualNotes.value = '';
        }
    }

    render() {
        this.syncInput();

        this.itemsTable.innerHTML = '';

        if (this.items.length === 0) {
            const row = document.createElement('tr');
            row.className = 'empty-placeholder';
            row.innerHTML = '<td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Aún no agregaste productos. Usa el catálogo o crea uno manual.</td>';
            this.itemsTable.appendChild(row);
            return;
        }

        this.items.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2">
                    <p class="font-medium text-slate-800">${this.escapeHtml(item.product_name ?? item.name)}</p>
                    <p class="text-xs text-slate-500">${item.type === 'manual' ? 'Creado manualmente' : 'Catálogo'}</p>
                </td>
                <td class="px-4 py-2">${item.quantity} ${this.escapeHtml(item.quantity_unit ?? '')}</td>
                <td class="px-4 py-2 text-sm text-slate-600">${this.escapeHtml(item.supermarket_name ?? 'Por definir')}</td>
                <td class="px-4 py-2 text-sm text-slate-600">${this.escapeHtml(item.section_name ?? '')}</td>
                <td class="px-4 py-2 text-sm text-slate-700">${this.formatPrice(item.quantity, item.estimated_price)}</td>
                <td class="px-4 py-2 text-right">
                    <button type="button" data-action="remove-item" data-index="${index}" class="text-sm text-rose-600 hover:underline">Eliminar</button>
                </td>
            `;
            this.itemsTable.appendChild(row);
        });
    }

    formatPrice(quantity, estimatedPrice) {
        if (estimatedPrice === null || estimatedPrice === undefined) {
            return '—';
        }

        const total = (estimatedPrice || 0) * (quantity || 1);

        return `$${total.toFixed(2)}`;
    }

    syncInput() {
        if (this.itemsInput) {
            this.itemsInput.value = JSON.stringify(this.items);
        }
    }

    escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return value
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-list-builder]').forEach((element) => {
        new ListBuilder(element);
    });
});
