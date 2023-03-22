import { TestBed } from '@angular/core/testing';

import { ApprentiGuard } from './apprenti.guard';

describe('ApprentiGuard', () => {
  let guard: ApprentiGuard;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    guard = TestBed.inject(ApprentiGuard);
  });

  it('should be created', () => {
    expect(guard).toBeTruthy();
  });
});
