import './bootstrap';

class ProductCatalog {
    constructor(root) {
        this.root = root;
        this.products = this.parseDataset(root.dataset.products);
        this.lists = this.parseDataset(root.dataset.lists);
        this.sections = this.parseDataset(root.dataset.sections);
        this.productById = new Map(this.products.map((product) => [product.id, product]));
        this.listById = new Map(this.lists.map((list) => [list.id, list]));
        this.sectionById = new Map(this.sections.map((section) => [section.id, section]));
        this.sectionsBySupermarket = this.buildSectionsIndex();

        this.modal = document.querySelector('[data-modal="product-add"]');
        this.form = this.modal?.querySelector('[data-product-add-form]');
        this.itemsInput = this.form?.querySelector('[data-add-items]');
        this.listSelect = this.form?.querySelector('[data-add-list]');
        this.quantityInput = this.form?.querySelector('[data-add-quantity]');
        this.priceInput = this.form?.querySelector('[data-add-price]');
        this.notesInput = this.form?.querySelector('[data-add-notes]');
        this.sectionSelect = this.form?.querySelector('[data-add-section]');
        this.summaryNode = this.form?.querySelector('[data-add-summary]');
        this.productNameNode = this.form?.querySelector('[data-add-product-name]');
        this.productMetaNode = this.form?.querySelector('[data-add-product-meta]');
        this.productDescriptionNode = this.form?.querySelector('[data-add-product-description]');
        this.unitBadge = this.form?.querySelector('[data-add-unit]');
        this.sectionHint = this.form?.querySelector('[data-add-section-hint]');
        this.submitButton = this.form?.querySelector('[data-add-submit]');
        this.bodyContainer = this.form?.querySelector('[data-add-body]');
        this.emptyContainer = this.form?.querySelector('[data-add-empty]');
        this.actionTemplate = this.form?.dataset.actionTemplate ?? '';
        this.currentProduct = null;

        this.bindEvents();
    }

    parseDataset(value) {
        if (! value) {
            return [];
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            console.warn('No se pudo interpretar el dataset del catálogo', error);
            return [];
        }
    }

    buildSectionsIndex() {
        const index = new Map();

        this.sections.forEach((section) => {
            const list = index.get(section.supermarket_id) ?? [];
            list.push(section);
            index.set(section.supermarket_id, list);
        });

        return index;
    }

    bindEvents() {
        this.root.querySelectorAll('[data-action="open-add"]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopImmediatePropagation();

                const card = button.closest('[data-product-card]');
                const productId = this.parseNullableInt(button.dataset.productId ?? card?.dataset.productId ?? null);

                if (productId) {
                    this.openModalForProduct(productId);
                }
            });
        });

        if (this.listSelect) {
            this.listSelect.addEventListener('change', () => {
                this.handleListChange();
            });
        }

        this.sectionSelect?.addEventListener('change', () => this.updateItemsPayload());
        this.quantityInput?.addEventListener('input', () => this.updateItemsPayload());
        this.priceInput?.addEventListener('input', () => this.updateItemsPayload());
        this.notesInput?.addEventListener('input', () => this.updateItemsPayload());

        this.form?.addEventListener('submit', (event) => {
            const payload = this.buildPayload();

            if (! payload) {
                event.preventDefault();
                alert('Selecciona una lista activa antes de continuar.');
                return false;
            }

            this.syncItemsInput(payload);

            return true;
        });
    }

    openModalForProduct(productId) {
        const product = this.productById.get(productId);

        if (! product || ! this.modal || ! this.form) {
            return;
        }

        this.currentProduct = product;
        this.resetFormState(product);
        this.populateListOptions();
        this.updateProductDetails(product);
        this.handleListChange();
        this.modal.classList.remove('hidden');
    }

    resetFormState(product) {
        if (this.listSelect) {
            this.listSelect.value = '';
        }

        if (this.quantityInput) {
            this.quantityInput.value = '1';
        }

        if (this.priceInput) {
            this.priceInput.value = product?.average_price ?? '';
        }

        if (this.notesInput) {
            this.notesInput.value = '';
        }

        if (this.sectionSelect) {
            this.sectionSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Sin pasillo asignado';
            this.sectionSelect.appendChild(placeholder);
        }

        if (this.summaryNode) {
            this.summaryNode.textContent = 'Selecciona una lista activa para preparar el envío del producto.';
        }
    }

    populateListOptions() {
        if (! this.listSelect) {
            return;
        }

        this.listSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- Selecciona una lista --';
        this.listSelect.appendChild(placeholder);

        this.lists.forEach((list) => {
            const option = document.createElement('option');
            option.value = list.id;
            option.textContent = list.supermarket_name
                ? `${list.name} · ${list.supermarket_name}`
                : list.name;
            this.listSelect?.appendChild(option);
        });
    }

    updateProductDetails(product) {
        if (this.productNameNode) {
            this.productNameNode.textContent = product.name;
        }

        const unit = product.unit ?? 'Sin unidad';
        const brand = product.brand ?? 'Sin marca';

        if (this.productMetaNode) {
            this.productMetaNode.textContent = `${brand} · ${unit}`;
        }

        if (this.productDescriptionNode) {
            const description = product.description ?? 'Sin descripción registrada.';
            this.productDescriptionNode.textContent = description;
        }

        if (this.unitBadge) {
            this.unitBadge.textContent = product.unit ?? 'Unidad';
        }
    }

    handleListChange() {
        if (! this.listSelect) {
            return;
        }

        const listId = this.parseNullableInt(this.listSelect.value);
        const list = listId ? this.listById.get(listId) : null;

        if (! list) {
            this.updateSectionOptions(null);
            this.updateItemsPayload();
            this.updateFormAvailability(false);
            return;
        }

        this.updateSectionOptions(list);
        this.updateItemsPayload();
        this.updateFormAvailability(true);
    }

    updateSectionOptions(list) {
        if (! this.sectionSelect) {
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Sin pasillo asignado';

        this.sectionSelect.innerHTML = '';
        this.sectionSelect.appendChild(placeholder);

        if (! list) {
            this.sectionSelect.disabled = true;

            if (this.sectionHint) {
                this.sectionHint.textContent = 'Selecciona una lista activa para ver los pasillos disponibles.';
            }

            return;
        }

        if (! list.supermarket_id) {
            this.sectionSelect.disabled = true;

            if (this.sectionHint) {
                this.sectionHint.textContent = 'Asigna un establecimiento predeterminado en la lista para elegir pasillos.';
            }

        } else {
            this.sectionSelect.disabled = false;

            const sections = this.sectionsBySupermarket.get(list.supermarket_id) ?? [];
            const defaultEntry = this.currentProduct
                ? this.getInventoryEntry(this.currentProduct.id, list.supermarket_id)
                : null;

            sections.forEach((section) => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = this.formatSection(section);

                if (defaultEntry?.section_id && defaultEntry.section_id === section.id) {
                    option.selected = true;
                }

                this.sectionSelect?.appendChild(option);
            });

            if (defaultEntry?.section_id && this.sectionSelect && ! this.sectionSelect.value) {
                this.sectionSelect.value = String(defaultEntry.section_id);
            }

            if (this.sectionHint) {
                this.sectionHint.textContent = list.supermarket_name
                    ? `Pasillos del establecimiento ${list.supermarket_name}.`
                    : 'Selecciona un pasillo disponible para esta lista.';
            }
        }
    }

    updateFormAvailability(isReady) {
        const hasLists = this.lists.length > 0;

        if (this.submitButton) {
            this.submitButton.disabled = ! isReady;
            this.submitButton.classList.toggle('opacity-60', ! isReady);
        }

        if (this.bodyContainer) {
            this.bodyContainer.classList.toggle('hidden', ! hasLists);
        }

        if (this.emptyContainer) {
            this.emptyContainer.classList.toggle('hidden', hasLists);
        }

        if (! isReady && hasLists && this.summaryNode) {
            this.summaryNode.textContent = 'Selecciona una lista activa para preparar el envío del producto.';
        }
    }

    updateItemsPayload() {
        const payload = this.buildPayload();

        if (payload) {
            this.syncItemsInput(payload);
            this.updateSummary(payload);
        } else if (this.itemsInput) {
            this.itemsInput.value = '[]';
        }
    }

    buildPayload() {
        if (! this.currentProduct || ! this.listSelect) {
            return null;
        }

        const listId = this.parseNullableInt(this.listSelect.value);
        const list = listId ? this.listById.get(listId) : null;

        if (! list) {
            return null;
        }

        const quantity = this.quantityInput ? Number.parseFloat(this.quantityInput.value || '1') || 1 : 1;
        const price = this.priceInput ? this.parsePrice(this.priceInput.value) : null;
        const notes = this.notesInput?.value?.trim() || null;
        const sectionId = this.sectionSelect ? this.parseNullableInt(this.sectionSelect.value) : null;
        const sectionData = sectionId ? this.sectionById.get(sectionId) : null;
        const inventoryEntry = this.getInventoryEntry(this.currentProduct.id, list.supermarket_id);

        return {
            type: 'existing',
            product_id: this.currentProduct.id,
            product_name: this.currentProduct.name,
            name: this.currentProduct.name,
            brand: this.currentProduct.brand ?? null,
            product_category_id: this.currentProduct.product_category_id ?? this.currentProduct.category_id ?? null,
            package_size: this.currentProduct.package_size ?? null,
            unit: this.currentProduct.unit ?? '',
            quantity,
            quantity_unit: this.currentProduct.unit ?? '',
            estimated_price: price ?? null,
            supermarket_id: list.supermarket_id ?? null,
            supermarket_name: list.supermarket_name ?? null,
            section_id: sectionId ?? inventoryEntry?.section_id ?? null,
            section_name: sectionData?.name ?? inventoryEntry?.section_name ?? null,
            section_number: sectionData?.position ?? inventoryEntry?.section_position ?? null,
            notes,
            description: this.currentProduct.description ?? null,
        };
    }

    syncItemsInput(payload) {
        if (this.itemsInput) {
            this.itemsInput.value = JSON.stringify([payload]);
        }

        if (this.form && this.actionTemplate) {
            const listId = this.parseNullableInt(this.listSelect?.value ?? null);

            if (listId) {
                this.form.action = this.actionTemplate.replace('__LIST__', String(listId));
            }
        }
    }

    updateSummary(payload) {
        if (! this.summaryNode) {
            return;
        }

        const supermarket = payload.supermarket_name ?? 'Sin establecimiento principal';
        const sectionLabel = payload.section_number
            ? `Pasillo ${payload.section_number}${payload.section_name ? ` · ${payload.section_name}` : ''}`
            : (payload.section_name ?? 'Sin pasillo asignado');

        this.summaryNode.textContent = `Se agregará ${payload.quantity} ${payload.quantity_unit || ''} de ${payload.product_name} a ${supermarket}. Ubicación: ${sectionLabel}.`;
    }

    getInventoryEntry(productId, supermarketId) {
        if (! productId) {
            return null;
        }

        const product = this.productById.get(productId);

        if (! product || ! Array.isArray(product.inventory)) {
            return null;
        }

        if (supermarketId) {
            const match = product.inventory.find((entry) => entry.supermarket_id === supermarketId);

            if (match) {
                return match;
            }
        }

        return product.inventory[0] ?? null;
    }

    formatSection(section) {
        if (section.position !== null && section.position !== undefined) {
            return section.name ? `Pasillo ${section.position} · ${section.name}` : `Pasillo ${section.position}`;
        }

        return section.name ?? 'Sin pasillo';
    }

    parseNullableInt(value) {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const parsed = Number.parseInt(value, 10);

        return Number.isNaN(parsed) ? null : parsed;
    }

    parsePrice(value) {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const parsed = Number.parseFloat(value);

        return Number.isNaN(parsed) ? null : parsed;
    }
}

class ListBuilder {
    constructor(root) {
        this.root = root;
        this.itemsInput = document.getElementById('items-input');
        this.displayMode = root.dataset.displayMode ?? 'inline';
        this.products = this.parseDataset(root.dataset.products);
        this.supermarkets = this.parseDataset(root.dataset.supermarkets);
        this.sections = this.parseDataset(root.dataset.sections);
        this.productById = new Map(this.products.map((product) => [product.id, product]));
        this.supermarketById = new Map(this.supermarkets.map((market) => [market.id, market]));
        this.sectionById = new Map(this.sections.map((section) => [section.id, section]));
        this.sectionsBySupermarket = this.buildSectionsIndex();

        this.queueContainer = root.querySelector('[data-items-queue]');
        this.emptyPlaceholder = this.queueContainer?.querySelector('[data-empty-placeholder]') ?? null;
        this.itemTemplate = root.querySelector('[data-item-template]');
        this.items = this.parseInitialItems(this.itemsInput?.value ?? '[]');

        this.existingForms = this.collectExistingForms();
        this.manualForms = this.collectManualForms();
        this.modals = this.collectModals();

        this.defaultSupermarketId = this.parseNullableInt(root.dataset.defaultSupermarket);
        this.supermarketField = root.dataset.supermarketField
            ? document.querySelector(root.dataset.supermarketField)
            : null;

        if (this.supermarketField) {
            this.supermarketField.addEventListener('change', () => {
                this.defaultSupermarketId = this.parseNullableInt(this.supermarketField.value);
                this.manualForms.forEach((form) => this.populateManualSections(form));
            });
        }

        this.bindEvents();
        this.render();
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

    parseInitialItems(value) {
        if (! value) {
            return [];
        }

        try {
            const parsed = JSON.parse(value);

            if (! Array.isArray(parsed)) {
                return [];
            }

            return parsed.map((item) => ({
                ...item,
                quantity: Number.parseFloat(item.quantity ?? 1) || 1,
                estimated_price: this.parsePrice(item.estimated_price),
                quantity_unit: item.quantity_unit ?? item.unit ?? '',
                supermarket_id: this.parseNullableInt(item.supermarket_id),
                section_id: this.parseNullableInt(item.section_id),
                section_number: this.parseNullableInt(item.section_number),
                notes: item.notes ?? null,
            }));
        } catch (error) {
            console.warn('No se pudieron leer los productos iniciales', error);
            return [];
        }
    }

    collectExistingForms() {
        return Array.from(this.root.querySelectorAll('[data-existing-block]')).map((container) => ({
            container,
            select: container.querySelector('[data-existing-product]'),
            quantity: container.querySelector('[data-existing-quantity]'),
            notes: container.querySelector('[data-existing-notes]'),
            filter: container.querySelector('[data-existing-filter]'),
        }));
    }

    collectManualForms() {
        return Array.from(this.root.querySelectorAll('[data-manual-block]')).map((container) => ({
            container,
            name: container.querySelector('[data-manual-name]'),
            unit: container.querySelector('[data-manual-unit]'),
            brand: container.querySelector('[data-manual-brand]'),
            quantity: container.querySelector('[data-manual-quantity]'),
            price: container.querySelector('[data-manual-price]'),
            supermarket: container.querySelector('[data-manual-supermarket]'),
            section: container.querySelector('[data-manual-section]'),
            notes: container.querySelector('[data-manual-notes]'),
            addButton: container.querySelector('[data-action="add-manual"]'),
        }));
    }

    collectModals() {
        const modals = new Map();

        this.root.querySelectorAll('[data-modal]').forEach((modal) => {
            const id = modal.dataset.modal;

            if (! id) {
                return;
            }

            modals.set(id, modal);

            modal.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => this.closeModal(modal));
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    this.closeModal(modal);
                }
            });
        });

        return modals;
    }

    buildSectionsIndex() {
        const index = new Map();

        this.sections.forEach((section) => {
            const list = index.get(section.supermarket_id) ?? [];
            list.push(section);
            index.set(section.supermarket_id, list);
        });

        return index;
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

        this.root.querySelectorAll('[data-action="open-existing"]').forEach((button) => {
            button.addEventListener('click', () => this.handleOpenExisting());
        });

        this.root.querySelectorAll('[data-action="open-manual"]').forEach((button) => {
            button.addEventListener('click', () => this.handleOpenManual());
        });

        this.existingForms.forEach((form) => {
            form.select?.addEventListener('change', () => this.handleExistingSelection(form));
            form.filter?.addEventListener('input', () => this.filterExistingOptions(form));

            this.applyExistingFilters(form);
        });

        this.manualForms.forEach((form) => {
            form.addButton?.addEventListener('click', () => this.addManualItem(form));
            form.supermarket?.addEventListener('change', () => this.populateManualSections(form));
            this.populateManualSections(form);
        });

        this.queueContainer?.addEventListener('click', (event) => {
            const target = event.target;

            if (! (target instanceof HTMLElement)) {
                return;
            }

            if (target.dataset.action === 'remove-item') {
                const index = Number.parseInt(target.dataset.index ?? '', 10);

                if (! Number.isNaN(index)) {
                    this.items.splice(index, 1);
                    this.render();
                }
            }

            if (target.dataset.action === 'edit-item') {
                const index = Number.parseInt(target.dataset.index ?? '', 10);

                if (! Number.isNaN(index)) {
                    this.openEditModal(index);
                }
            }
        });
    }

    handleOpenExisting() {
        if (this.displayMode === 'modal') {
            this.openModal('builder-existing');
            return;
        }

        const form = this.existingForms[0];

        if (form?.filter) {
            form.filter.focus();
        } else if (form?.select) {
            form.select.focus();
        }
    }

    handleOpenManual() {
        if (this.displayMode === 'modal') {
            this.openModal('builder-manual');
            return;
        }

        const form = this.manualForms[0];

        if (form?.name) {
            form.name.focus();
        }
    }

    filterExistingOptions(form) {
        this.applyExistingFilters(form);
    }

    applyExistingFilters(form) {
        const select = form.select;

        if (! select) {
            return;
        }

        const query = form.filter?.value?.trim().toLowerCase() ?? '';

        Array.from(select.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const text = option.textContent?.toLowerCase() ?? '';
            const matchesQuery = query === '' || text.includes(query);
            option.hidden = ! matchesQuery;
        });
    }

    handleExistingSelection(form) {
        if (! form.select) {
            return;
        }

        const productId = this.parseNullableInt(form.select.value);
        const product = productId ? this.productById.get(productId) : null;

        if (! product) {
            return;
        }

        const quantity = form.quantity ? Number.parseFloat(form.quantity.value || '1') || 1 : 1;
        const notes = form.notes?.value?.trim() || null;

        const supermarketId = this.resolveDefaultSupermarketId(product);
        const inventoryEntry = this.resolveInventoryEntry(product, supermarketId);

        const item = {
            type: 'existing',
            product_id: product.id,
            product_name: product.name,
            name: product.name,
            brand: product.brand ?? null,
            description: product.description ?? null,
            unit: product.unit ?? '',
            product_category_id: product.product_category_id ?? product.category_id ?? null,
            package_size: product.package_size ?? null,
            quantity,
            quantity_unit: product.unit ?? '',
            estimated_price: this.parsePrice(product.average_price) ?? null,
            supermarket_id: supermarketId,
            supermarket_name: this.resolveSupermarketName(supermarketId),
            section_id: inventoryEntry?.section_id ?? null,
            section_name: inventoryEntry?.section_name ?? null,
            section_number: inventoryEntry?.section_position ?? null,
            notes,
        };

        this.items.push(item);
        this.render();

        if (form.select) {
            form.select.value = '';
        }

        if (form.notes) {
            form.notes.value = '';
        }

        if (form.quantity) {
            form.quantity.value = '1';
        }
    }

    resolveDefaultSupermarketId(product) {
        if (this.defaultSupermarketId) {
            return this.defaultSupermarketId;
        }

        if (Array.isArray(product.inventory) && product.inventory.length > 0) {
            return product.inventory[0].supermarket_id ?? null;
        }

        return null;
    }

    resolveInventoryEntry(product, supermarketId) {
        return this.getProductInventoryEntry(product?.id ?? null, supermarketId);
    }

    getProductInventoryEntry(productId, supermarketId) {
        if (! productId) {
            return null;
        }

        const product = this.productById.get(productId);

        if (! product || ! Array.isArray(product.inventory)) {
            return null;
        }

        if (supermarketId) {
            const match = product.inventory.find((item) => item.supermarket_id === supermarketId);

            if (match) {
                return match;
            }
        }

        return product.inventory[0] ?? null;
    }

    populateManualSections(form, selectedSectionId = null) {
        if (! form.section) {
            return;
        }

        const selectedSupermarket = this.parseNullableInt(form.supermarket?.value);
        const targetSupermarket = selectedSupermarket ?? this.defaultSupermarketId;
        const sections = targetSupermarket ? this.sectionsBySupermarket.get(targetSupermarket) ?? [] : [];

        form.section.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecciona un pasillo';
        form.section.appendChild(placeholder);

        sections.forEach((section) => {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = this.formatSectionOption(section);

            if (selectedSectionId && selectedSectionId === section.id) {
                option.selected = true;
            }

            form.section.appendChild(option);
        });
    }

    formatSectionOption(section) {
        if (section.position !== null && section.position !== undefined) {
            return section.name
                ? `Pasillo ${section.position} · ${section.name}`
                : `Pasillo ${section.position}`;
        }

        return section.name ?? 'Sin pasillo';
    }

    addManualItem(form) {
        if (! form.name || ! form.unit) {
            return;
        }

        const name = form.name.value.trim();
        const unit = form.unit.value.trim();

        if (! name || ! unit) {
            alert('Los productos manuales necesitan un nombre y una unidad de medida.');
            return;
        }

        const quantity = form.quantity ? Number.parseFloat(form.quantity.value || '1') || 1 : 1;
        const price = form.price ? this.parsePrice(form.price.value) : null;
        const supermarketId = this.parseNullableInt(form.supermarket?.value) ?? this.defaultSupermarketId;
        const sectionId = this.parseNullableInt(form.section?.value);
        const notes = form.notes?.value?.trim() || null;

        const item = {
            type: 'manual',
            name,
            product_name: name,
            unit,
            brand: form.brand?.value?.trim() || null,
            quantity,
            quantity_unit: unit,
            estimated_price: price,
            supermarket_id: supermarketId,
            supermarket_name: this.resolveSupermarketName(supermarketId),
            section_id: sectionId,
            section_name: this.resolveSectionName(sectionId),
            section_number: this.sectionById.get(sectionId)?.position ?? null,
            notes,
        };

        this.items.push(item);
        this.render();
        this.resetManualForm(form);
    }

    resetManualForm(form) {
        form.name && (form.name.value = '');
        form.unit && (form.unit.value = '');
        form.brand && (form.brand.value = '');
        form.quantity && (form.quantity.value = '1');
        form.price && (form.price.value = '');
        form.section && (form.section.value = '');
        form.notes && (form.notes.value = '');

        if (form.supermarket) {
            form.supermarket.value = '';
        }

        this.populateManualSections(form);
    }

    openEditModal(index) {
        const item = this.items[index];
        const modal = this.modals.get('builder-edit');
        const container = modal?.querySelector('[data-edit-form-container]');

        if (! item || ! modal || ! container) {
            return;
        }

        container.innerHTML = '';

        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nombre</label>
                    <input type="text" data-edit-name class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" ${item.type === 'existing' ? 'readonly' : ''} value="${this.escapeHtmlAttribute(item.product_name ?? item.name ?? '')}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Cantidad</label>
                    <input type="number" step="0.01" min="0.01" data-edit-quantity class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="${item.quantity}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Unidad</label>
                    <input type="text" data-edit-unit class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="${this.escapeHtmlAttribute(item.quantity_unit ?? '')}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Marca</label>
                    <input type="text" data-edit-brand class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="${this.escapeHtmlAttribute(item.brand ?? '')}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Precio estimado (unidad)</label>
                    <input type="number" step="0.01" min="0" data-edit-price class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="${item.estimated_price ?? ''}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Descripción</label>
                    <textarea rows="3" data-edit-description class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">${this.escapeHtml(item.description ?? '')}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Establecimiento</label>
                    <select data-edit-supermarket class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Pasillo / sección</label>
                    <select data-edit-section class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Notas</label>
                    <textarea rows="2" data-edit-notes class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">${this.escapeHtml(item.notes ?? '')}</textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" data-close-modal class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button type="button" data-action="save-edit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-500">Guardar cambios</button>
            </div>
        `;

        container.appendChild(wrapper);

        const nameInput = container.querySelector('[data-edit-name]');
        const quantityInput = container.querySelector('[data-edit-quantity]');
        const unitInput = container.querySelector('[data-edit-unit]');
        const brandInput = container.querySelector('[data-edit-brand]');
        const priceInput = container.querySelector('[data-edit-price]');
        const supermarketSelect = container.querySelector('[data-edit-supermarket]');
        const sectionSelect = container.querySelector('[data-edit-section]');
        const notesInput = container.querySelector('[data-edit-notes]');
        const descriptionInput = container.querySelector('[data-edit-description]');
        const saveButton = container.querySelector('[data-action="save-edit"]');

        const initialSupermarketId = item.supermarket_id ?? this.defaultSupermarketId ?? null;
        this.populateSupermarketOptions(supermarketSelect, initialSupermarketId);

        if (supermarketSelect && initialSupermarketId && ! supermarketSelect.value) {
            supermarketSelect.value = String(initialSupermarketId);
        }

        const effectiveSupermarketId = this.parseNullableInt(supermarketSelect?.value) ?? null;
        const defaultEntry = this.getProductInventoryEntry(item.product_id, effectiveSupermarketId);
        const initialSectionId = item.section_id ?? defaultEntry?.section_id ?? null;

        this.populateEditSections(sectionSelect, effectiveSupermarketId, initialSectionId);

        if (initialSectionId && sectionSelect && ! sectionSelect.value) {
            sectionSelect.value = String(initialSectionId);
        }

        if (! item.supermarket_id && effectiveSupermarketId) {
            item.supermarket_id = effectiveSupermarketId;
            item.supermarket_name = this.resolveSupermarketName(effectiveSupermarketId);
        }

        supermarketSelect?.addEventListener('change', () => {
            const targetSupermarket = this.parseNullableInt(supermarketSelect.value);
            const entry = this.getProductInventoryEntry(item.product_id, targetSupermarket);
            this.populateEditSections(sectionSelect, targetSupermarket, entry?.section_id ?? null);
        });

        saveButton?.addEventListener('click', () => {
            const quantity = Number.parseFloat(quantityInput?.value || '1') || 1;
            const unit = unitInput?.value?.trim() || item.quantity_unit || '';
            const brand = brandInput?.value?.trim() || null;
            const price = this.parsePrice(priceInput?.value ?? null);
            const supermarketId = this.parseNullableInt(supermarketSelect?.value) ?? null;
            const sectionId = this.parseNullableInt(sectionSelect?.value) ?? null;
            const notes = notesInput?.value?.trim() || null;
            const description = descriptionInput?.value?.trim() || null;

            if (item.type === 'manual' && nameInput) {
                item.name = nameInput.value.trim();
                item.product_name = item.name;
            }

            item.quantity = quantity;
            item.quantity_unit = unit;
            item.brand = brand;
            item.estimated_price = price;
            item.supermarket_id = supermarketId;
            item.supermarket_name = this.resolveSupermarketName(supermarketId);
            item.section_id = sectionId;
            item.section_name = this.resolveSectionName(sectionId);
            item.section_number = this.sectionById.get(sectionId)?.position ?? null;
            item.notes = notes;
            item.description = description;

            this.render();
            this.updateProductRecord(item);
            this.closeModal(modal);
        });

        this.openModal('builder-edit');
    }

    populateSupermarketOptions(select, selectedId) {
        if (! select) {
            return;
        }

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Usar predeterminado';
        select.appendChild(placeholder);

        this.supermarkets.forEach((market) => {
            const option = document.createElement('option');
            option.value = market.id;
            option.textContent = market.name;

            if (selectedId && selectedId === market.id) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    populateEditSections(select, supermarketId, selectedSectionId) {
        if (! select) {
            return;
        }

        const sections = supermarketId ? this.sectionsBySupermarket.get(supermarketId) ?? [] : [];

        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Sin pasillo';
        select.appendChild(placeholder);

        sections.forEach((section) => {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = this.formatSectionOption(section);

            if (selectedSectionId && selectedSectionId === section.id) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    openModal(id) {
        const modal = this.modals.get(id);

        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeModal(modal) {
        modal.classList.add('hidden');
    }

    parseNullableInt(value) {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const parsed = Number.parseInt(value, 10);

        return Number.isNaN(parsed) ? null : parsed;
    }

    parsePrice(value) {
        if (value === undefined || value === null || value === '') {
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
            return null;
        }

        return this.sectionById.get(id)?.name ?? null;
    }

    updateProductRecord(item) {
        if (item.type !== 'existing' || ! item.product_id || ! item.product_category_id || ! window.axios) {
            return;
        }

        const payload = {
            name: item.product_name ?? item.name ?? '',
            product_category_id: item.product_category_id,
            brand: item.brand || null,
            unit: item.quantity_unit || null,
            package_size: item.package_size || null,
            average_price: item.estimated_price ?? null,
            description: item.description || null,
        };

        window.axios
            .patch(`/products/${item.product_id}`, payload)
            .then((response) => {
                if (response?.data?.product) {
                    this.mergeProductData(response.data.product);
                } else {
                    this.mergeProductFromItem(item);
                }
            })
            .catch((error) => {
                console.warn('No se pudo sincronizar el producto', error);
                this.mergeProductFromItem(item);
            });
    }

    mergeProductData(data) {
        if (! data || data.id === undefined || data.id === null) {
            return;
        }

        const product = this.productById.get(data.id);

        if (! product) {
            this.productById.set(data.id, {
                ...data,
                average_price: data.average_price !== null && data.average_price !== undefined
                    ? Number.parseFloat(data.average_price)
                    : null,
                inventory: [],
            });

            return;
        }

        if (data.name !== undefined) {
            product.name = data.name;
        }

        if (data.brand !== undefined) {
            product.brand = data.brand;
        }

        if (data.unit !== undefined) {
            product.unit = data.unit;
        }

        if (data.package_size !== undefined) {
            product.package_size = data.package_size;
        }

        if (data.description !== undefined) {
            product.description = data.description;
        }

        if (data.average_price !== undefined) {
            product.average_price = data.average_price !== null
                ? Number.parseFloat(data.average_price)
                : null;
        }

        if (data.product_category_id !== undefined) {
            product.product_category_id = data.product_category_id;
            product.category_id = data.product_category_id;
        }
    }

    mergeProductFromItem(item) {
        if (! item.product_id) {
            return;
        }

        this.mergeProductData({
            id: item.product_id,
            name: item.product_name ?? item.name ?? null,
            brand: item.brand ?? null,
            unit: item.quantity_unit ?? null,
            package_size: item.package_size ?? null,
            average_price: item.estimated_price ?? null,
            description: item.description ?? null,
            product_category_id: item.product_category_id ?? null,
        });
    }

    render() {
        this.syncInput();

        if (! this.queueContainer) {
            return;
        }

        Array.from(this.queueContainer.querySelectorAll('[data-rendered-item]')).forEach((element) => {
            element.remove();
        });

        if (this.items.length === 0) {
            this.emptyPlaceholder?.classList.remove('hidden');
            return;
        }

        this.emptyPlaceholder?.classList.add('hidden');

        this.items.forEach((item, index) => {
            const element = this.createQueueItem(item, index);
            this.queueContainer.appendChild(element);
        });
    }

    createQueueItem(item, index) {
        const template = this.itemTemplate?.content?.firstElementChild;
        const element = template ? template.cloneNode(true) : document.createElement('article');

        element.dataset.renderedItem = 'true';
        element.dataset.index = String(index);

        const nameNode = element.querySelector('[data-item-name]');
        const metaNode = element.querySelector('[data-item-meta]');
        const typeNode = element.querySelector('[data-item-type]');
        const descriptionNode = element.querySelector('[data-item-description]');
        const notesNode = element.querySelector('[data-item-notes]');
        const editButton = element.querySelector('[data-action="edit-item"]');
        const removeButton = element.querySelector('[data-action="remove-item"]');

        if (nameNode) {
            nameNode.textContent = item.product_name ?? item.name ?? 'Producto';
        }

        if (metaNode) {
            metaNode.textContent = this.formatMeta(item);
        }

        if (typeNode) {
            typeNode.textContent = item.type === 'manual' ? 'Manual' : 'Catálogo';
        }

        if (descriptionNode) {
            descriptionNode.textContent = this.formatDescription(item);
        }

        if (notesNode) {
            if (item.notes) {
                notesNode.textContent = `Notas: ${item.notes}`;
                notesNode.classList.remove('hidden');
            } else {
                notesNode.textContent = '';
                notesNode.classList.add('hidden');
            }
        }

        if (editButton) {
            editButton.dataset.index = String(index);
        }

        if (removeButton) {
            removeButton.dataset.index = String(index);
        }

        return element;
    }

    formatMeta(item) {
        const quantityLabel = `${item.quantity} ${item.quantity_unit ?? ''}`.trim();
        const priceLabel = item.estimated_price !== null && item.estimated_price !== undefined
            ? `· Estimado $${(item.estimated_price * item.quantity).toFixed(2)}`
            : '';
        const brandLabel = item.brand ? `· ${item.brand}` : '';

        return [quantityLabel, brandLabel, priceLabel].filter(Boolean).join(' ');
    }

    formatDescription(item) {
        const description = item.description ?? 'Sin descripción';
        const location = this.formatLocation(item);

        return `Descripción: ${description}. Ubicación: ${location}`;
    }

    formatLocation(item) {
        const supermarket = item.supermarket_name ?? 'Por definir';
        const sectionParts = [];

        if (item.section_number !== null && item.section_number !== undefined) {
            sectionParts.push(`Pasillo ${item.section_number}`);
        }

        if (item.section_name) {
            sectionParts.push(item.section_name);
        }

        const sectionLabel = sectionParts.length > 0 ? sectionParts.join(' · ') : 'Sin pasillo';

        return `${supermarket} · ${sectionLabel}`;
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

    escapeHtmlAttribute(value) {
        return this.escapeHtml(value ?? '').replace(/"/g, '&quot;');
    }
}

class SectionBuilder {
    constructor(root) {
        this.root = root;
        this.sections = this.parseInitialSections(root.dataset.sections ?? '[]');
        this.elements = {
            number: root.querySelector('[data-section-number]'),
            name: root.querySelector('[data-section-name]'),
            addButton: root.querySelector('[data-add-section]'),
            list: root.querySelector('[data-section-list]'),
            hidden: root.querySelector('[data-section-hidden]'),
        };

        this.bindEvents();
        this.render();
    }

    parseInitialSections(value) {
        try {
            const parsed = JSON.parse(value);

            if (Array.isArray(parsed)) {
                return parsed
                    .map((item) => ({
                        id: item.id !== undefined && item.id !== null && item.id !== ''
                            ? Number.parseInt(item.id, 10)
                            : null,
                        number: Number.parseInt(item.number, 10),
                        name: item.name ?? '',
                    }))
                    .filter((item) => ! Number.isNaN(item.number) && item.name);
            }
        } catch (error) {
            console.warn('No se pudieron leer los pasillos iniciales', error);
        }

        return [];
    }

    bindEvents() {
        this.elements.addButton?.addEventListener('click', () => this.addSection());

        this.elements.list?.addEventListener('click', (event) => {
            const target = event.target;

            if (target instanceof HTMLElement && target.dataset.action === 'remove-section') {
                const index = Number.parseInt(target.dataset.index ?? '', 10);

                if (! Number.isNaN(index)) {
                    this.sections.splice(index, 1);
                    this.render();
                }
            }
        });
    }

    addSection() {
        const number = this.parseNumber(this.elements.number?.value ?? '');
        const name = (this.elements.name?.value || '').trim();

        if (number === null) {
            alert('Indica el número de pasillo.');
            return;
        }

        if (! name) {
            alert('Describe qué hay en el pasillo.');
            return;
        }

        this.sections.push({ id: null, number, name });
        this.sections.sort((a, b) => a.number - b.number);

        if (this.elements.number) {
            this.elements.number.value = '';
        }

        if (this.elements.name) {
            this.elements.name.value = '';
        }

        this.render();
    }

    parseNumber(value) {
        if (value === '') {
            return null;
        }

        const parsed = Number.parseInt(value, 10);

        return Number.isNaN(parsed) ? null : parsed;
    }

    render() {
        if (! this.elements.list || ! this.elements.hidden) {
            return;
        }

        this.elements.list.innerHTML = '';
        this.elements.hidden.innerHTML = '';

        if (this.sections.length === 0) {
            const placeholder = document.createElement('p');
            placeholder.className = 'text-xs text-slate-500';
            placeholder.textContent = 'Aún no agregaste pasillos.';
            this.elements.list.appendChild(placeholder);
        } else {
            this.sections.forEach((section, index) => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700';
                row.innerHTML = `
                    <div>
                        <p class="font-medium text-slate-800">Pasillo ${section.number}</p>
                        <p class="text-xs text-slate-500">${this.escapeHtml(section.name)}</p>
                    </div>
                    <button type="button" data-action="remove-section" data-index="${index}" class="text-xs font-semibold text-rose-600 hover:underline">Quitar</button>
                `;
                this.elements.list.appendChild(row);
            });
        }

        this.sections.forEach((section, index) => {
            const numberInput = document.createElement('input');
            numberInput.type = 'hidden';
            numberInput.name = `sections[${index}][number]`;
            numberInput.value = section.number;
            this.elements.hidden.appendChild(numberInput);

            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = `sections[${index}][name]`;
            nameInput.value = section.name;
            this.elements.hidden.appendChild(nameInput);

            if (section.id !== null && section.id !== undefined && ! Number.isNaN(section.id)) {
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = `sections[${index}][id]`;
                idInput.value = section.id;
                this.elements.hidden.appendChild(idInput);
            }
        });
    }

    escapeHtml(value) {
        return value
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-product-catalog]').forEach((element) => {
        new ProductCatalog(element);
    });

    document.querySelectorAll('[data-list-builder]').forEach((element) => {
        new ListBuilder(element);
    });

    document.querySelectorAll('[data-section-builder]').forEach((element) => {
        new SectionBuilder(element);
    });
});
