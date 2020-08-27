import { createLocalVue, shallowMount } from '@vue/test-utils';
import 'src/app/component/form/select/base/sw-select-selection-list';

function createWrapper(propsData = {}) {
    const localVue = createLocalVue();
    localVue.directive('tooltip', {});

    return shallowMount(Shopware.Component.build('sw-select-selection-list'), {
        localVue,
        stubs: {
            'sw-label': '<div class="sw-label"><slot></slot></div>'
        },
        provide: {
        },
        mocks: {
            $tc: v => v
        },
        propsData: {
            ...propsData
        }
    });
}

describe('src/app/component/form/select/base/sw-select-selection-list', () => {
    it('should be a Vue.js component', () => {
        const wrapper = createWrapper();

        expect(wrapper.isVueInstance()).toBeTruthy();
    });

    it('should render dismissable labels', () => {
        const wrapper = createWrapper({
            selections: [{ label: 'Selection1' }]
        });

        const element = wrapper.find('.sw-label');
        expect(element.exists()).toBeTruthy();
        expect(element.attributes().dismissable).toBeTruthy();
    });

    it('should render labels which are not dismissable', () => {
        const wrapper = createWrapper({
            disabled: true,
            selections: [{ label: 'Selection1' }]
        });

        const element = wrapper.find('.sw-label');
        expect(element.exists()).toBeTruthy();
        expect(element.attributes().dismissable).toBeFalsy();
    });
});
