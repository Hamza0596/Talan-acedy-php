import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModuleAdminComponent } from './module-admin.component';

describe('ModuleAdminComponent', () => {
  let component: ModuleAdminComponent;
  let fixture: ComponentFixture<ModuleAdminComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ModuleAdminComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ModuleAdminComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
